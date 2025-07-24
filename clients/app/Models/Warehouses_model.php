<?php

namespace App\Models;

use App\Models\Crud_model;

class Warehouses_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'warehouses';
        parent::__construct($this->table);
    }

    /**
     * Get active warehouses
     */
    function get_active_warehouses() {
        return $this->get_all_where(array("is_active" => 1, "deleted" => 0))->getResult();
    }

    /**
     * Get warehouse by code
     */
    function get_warehouse_by_code($code) {
        return $this->get_one_where(array("code" => $code, "deleted" => 0));
    }

    /**
     * Get default warehouse
     */
    function get_default_warehouse() {
        // For now, warehouse with ID 1 is default
        return $this->get_one(1);
    }

    /**
     * Check if warehouse has inventory
     */
    function has_inventory($warehouse_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM inventory 
                WHERE warehouse_id = ? AND deleted = 0 
                AND quantity_on_hand > 0";
        
        $result = $this->db->query($sql, array($warehouse_id))->getRow();
        return $result->count > 0;
    }

    /**
     * Get warehouse inventory summary
     */
    function get_inventory_summary($warehouse_id) {
        $sql = "SELECT 
                COUNT(DISTINCT item_id) as total_items,
                SUM(quantity_on_hand) as total_quantity,
                SUM(quantity_on_hand * unit_cost) as total_value,
                COUNT(CASE WHEN quantity_available <= reorder_level AND reorder_level > 0 THEN 1 END) as low_stock_items,
                COUNT(CASE WHEN quantity_available = 0 THEN 1 END) as out_of_stock_items
                FROM inventory 
                WHERE warehouse_id = ? AND deleted = 0";
        
        return $this->db->query($sql, array($warehouse_id))->getRow();
    }

    /**
     * Transfer inventory between warehouses
     */
    function transfer_inventory($from_warehouse_id, $to_warehouse_id, $item_id, $quantity, $material_id = null) {
        // Get source inventory
        $where = array(
            "warehouse_id" => $from_warehouse_id,
            "item_id" => $item_id,
            "deleted" => 0
        );
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        $source_inventory = $this->db->table('inventory')->where($where)->get()->getRow();
        
        if (!$source_inventory || $source_inventory->quantity_available < $quantity) {
            return false;
        }
        
        // Update source warehouse
        $this->db->table('inventory')
            ->where('id', $source_inventory->id)
            ->update(array(
                'quantity_on_hand' => $source_inventory->quantity_on_hand - $quantity,
                'quantity_available' => $source_inventory->quantity_available - $quantity,
                'updated_at' => get_current_utc_time()
            ));
        
        // Get or create destination inventory
        $where['warehouse_id'] = $to_warehouse_id;
        $dest_inventory = $this->db->table('inventory')->where($where)->get()->getRow();
        
        if ($dest_inventory) {
            // Update existing
            $this->db->table('inventory')
                ->where('id', $dest_inventory->id)
                ->update(array(
                    'quantity_on_hand' => $dest_inventory->quantity_on_hand + $quantity,
                    'quantity_available' => $dest_inventory->quantity_available + $quantity,
                    'updated_at' => get_current_utc_time()
                ));
        } else {
            // Create new
            $data = array(
                'item_id' => $item_id,
                'material_id' => $material_id,
                'warehouse_id' => $to_warehouse_id,
                'quantity_on_hand' => $quantity,
                'quantity_available' => $quantity,
                'quantity_reserved' => 0,
                'unit_cost' => $source_inventory->unit_cost,
                'reorder_level' => $source_inventory->reorder_level,
                'reorder_quantity' => $source_inventory->reorder_quantity,
                'created_at' => get_current_utc_time()
            );
            
            $this->db->table('inventory')->insert($data);
        }
        
        // Create transaction records
        $this->create_transfer_transaction($source_inventory->id, $dest_inventory->id ?? null, $quantity);
        
        return true;
    }

    /**
     * Create transfer transaction records
     */
    private function create_transfer_transaction($source_inventory_id, $dest_inventory_id, $quantity) {
        // Source transaction
        $data = array(
            'inventory_id' => $source_inventory_id,
            'transaction_type' => 'transfer',
            'reference_type' => 'transfer_out',
            'reference_id' => $dest_inventory_id,
            'quantity' => -$quantity,
            'created_by' => $this->login_user->id,
            'created_at' => get_current_utc_time()
        );
        
        $this->db->table('inventory_transactions')->insert($data);
        
        // Destination transaction
        if ($dest_inventory_id) {
            $data['inventory_id'] = $dest_inventory_id;
            $data['reference_type'] = 'transfer_in';
            $data['reference_id'] = $source_inventory_id;
            $data['quantity'] = $quantity;
            
            $this->db->table('inventory_transactions')->insert($data);
        }
    }
}