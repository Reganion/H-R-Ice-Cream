<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Contract\Firestore as FirestoreContract;

class FirestoreService
{
    protected ?FirestoreClient $db = null;

    public function __construct(FirestoreContract $firestore)
    {
        $this->db = $firestore->database();
    }

    /**
     * Add a document with auto-generated ID. Returns the new document ID.
     * Adds created_at automatically.
     */
    public function add(string $collection, array $data): string
    {
        $data['created_at'] = date('c');
        $data['updated_at'] = date('c');
        $ref = $this->db->collection($collection)->add($this->toFirestore($data));
        return $ref->id();
    }

    /**
     * Get a document by ID. Returns array with 'id' and all fields, or null.
     */
    public function get(string $collection, string $id): ?array
    {
        $snap = $this->db->collection($collection)->document($id)->snapshot();
        if (!$snap->exists()) {
            return null;
        }
        return $this->snapshotToArray($snap);
    }

    /**
     * Set (create or overwrite) a document.
     */
    public function set(string $collection, string $id, array $data): void
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('c');
        }
        $data['updated_at'] = date('c');
        $this->db->collection($collection)->document($id)->set($this->toFirestore($data));
    }

    /**
     * Update a document (merge). Adds updated_at.
     */
    public function update(string $collection, string $id, array $data): void
    {
        $data['updated_at'] = date('c');
        $this->db->collection($collection)->document($id)->update($this->toFirestore($data));
    }

    /**
     * Delete a document.
     */
    public function delete(string $collection, string $id): void
    {
        $this->db->collection($collection)->document($id)->delete();
    }

    /**
     * Get all documents in a collection, optionally ordered.
     *
     * @param  string  $orderBy  Field name (e.g. 'created_at')
     * @param  string  $direction  'asc' or 'desc'
     * @return array<int, array>  List of doc arrays (each has 'id' key)
     */
    public function all(string $collection, ?string $orderBy = null, string $direction = 'desc'): array
    {
        $query = $this->db->collection($collection);
        if ($orderBy !== null) {
            $query = $query->orderBy($orderBy, $direction);
        }
        $snaps = $query->documents();
        $out = [];
        foreach ($snaps as $snap) {
            if ($snap->exists()) {
                $out[] = $this->snapshotToArray($snap);
            }
        }
        return $out;
    }

    /**
     * Find first document where field equals value.
     */
    public function firstWhere(string $collection, string $field, mixed $value): ?array
    {
        $query = $this->db->collection($collection)->where($field, '=', $value)->limit(1);
        $snaps = $query->documents();
        foreach ($snaps as $snap) {
            if ($snap->exists()) {
                return $this->snapshotToArray($snap);
            }
        }
        return null;
    }

    /**
     * Find documents where field equals value, optionally ordered.
     *
     * @return array<int, array>
     */
    public function where(string $collection, string $field, mixed $value, ?string $orderBy = null, string $direction = 'desc'): array
    {
        $query = $this->db->collection($collection)->where($field, '=', $value);
        if ($orderBy !== null) {
            $query = $query->orderBy($orderBy, $direction);
        }
        $snaps = $query->documents();
        $out = [];
        foreach ($snaps as $snap) {
            if ($snap->exists()) {
                $out[] = $this->snapshotToArray($snap);
            }
        }
        return $out;
    }

    /**
     * Convert DocumentSnapshot to array with 'id'.
     */
    protected function snapshotToArray($snapshot): array
    {
        $data = $snapshot->data();
        if (!is_array($data)) {
            $data = [];
        }
        foreach ($data as $k => $v) {
            if ($v instanceof \Google\Cloud\Firestore\Timestamp) {
                $data[$k] = $v->format('c');
            }
        }
        $data['id'] = $snapshot->id();
        return $data;
    }

    /**
     * Prepare array for Firestore (e.g. convert DateTime to string).
     */
    protected function toFirestore(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $out[$k] = $v->format('c');
            } elseif (is_array($v)) {
                $out[$k] = $this->toFirestore($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}
