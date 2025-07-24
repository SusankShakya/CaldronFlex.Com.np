<?php

namespace App\Models;

class ItemDimensions_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'item_dimensions';
        parent::__construct($this->table);
    }

    /**
     * Save or update dimensions for an item.
     * If dimensions exist for the item, update them; otherwise, insert new.
     * @param int $item_id
     * @param float $width
     * @param float $height
     * @param string $unit
     * @return bool
     */
    public function save_dimensions($item_id, $width, $height, $unit = 'cm')
    {
        $existing = $this->db->table($this->table)
            ->where('item_id', $item_id)
            ->get()
            ->getRow();

        $data = [
            'item_id' => $item_id,
            'width' => $width,
            'height' => $height,
            'unit' => $unit,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            return $this->db->table($this->table)
                ->where('item_id', $item_id)
                ->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->table($this->table)
                ->insert($data);
        }
    }

    /**
     * Get dimensions for an item.
     * @param int $item_id
     * @return object|null
     */
    public function get_dimensions($item_id)
    {
        return $this->db->table($this->table)
            ->where('item_id', $item_id)
            ->get()
            ->getRow();
    }
}