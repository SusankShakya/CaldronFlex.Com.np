<?php

namespace App\Models;

use App\Models\Crud_model;

class Inventory_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'inventory';
        parent::__construct($this->table);
    }

    /**
     * Get inventory for an item
     */
    function get_item_inventory($item_id, $material_id = null, $warehouse_id = null) {
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        if ($warehouse_id) {
            $where["warehouse_id"] = $warehouse_id;
        }
        
        return $this->get_all_where($where)->getResult();
    }

    /**
     * Get available quantity for an item
     */
    function get_available_quantity($item_id, $material_id = null, $warehouse_id = null) {
        $inventory = $this->get_item_inventory($item_id, $material_id, $warehouse_id);
        
        $total_available = 0;
        foreach ($inventory as $inv) {
            $total_available += $inv->quantity_available;
        }
        
        return $total_available;
    }

    /**
     * Check if item is in stock
     */
    function is_in_stock($item_id, $quantity_needed, $material_id = null, $warehouse_id = null) {
        $available = $this->get_available_quantity($item_id, $material_id, $warehouse_id);
        return $available >= $quantity_needed;
    }

    /**
     * Reserve inventory for an order
     */
    function reserve_inventory($item_id, $quantity, $order_id, $material_id = null, $warehouse_id = null) {
        // Get inventory record
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        if ($warehouse_id) {
            $where["warehouse_id"] = $warehouse_id;
        }
        
        $inventory = $this->get_one_where($where);
        
        if (!$inventory || $inventory->quantity_available < $quantity) {
            return false;
        }
        
        // Update quantities
        $data = array(
            'quantity_reserved' => $inventory->quantity_reserved + $quantity,
            'quantity_available' => $inventory->quantity_available - $quantity,
            'updated_at' => get_current_utc_time()
        );
        
        $this->ci->db->table($this->table)->where('id', $inventory->id)->update($data);
        
        // Create transaction record
        $this->create_transaction($inventory->id, 'sale', $quantity, $order_id);
        
        return true;
    }

    /**
     * Release reserved inventory
     */
    function release_reservation($item_id, $quantity, $order_id, $material_id = null, $warehouse_id = null) {
        // Get inventory record
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        if ($warehouse_id) {
            $where["warehouse_id"] = $warehouse_id;
        }
        
        $inventory = $this->get_one_where($where);
        
        if (!$inventory || $inventory->quantity_reserved < $quantity) {
            return false;
        }
        
        // Update quantities
        $data = array(
            'quantity_reserved' => $inventory->quantity_reserved - $quantity,
            'quantity_available' => $inventory->quantity_available + $quantity,
            'updated_at' => get_current_utc_time()
        );
        
        $this->ci->db->table($this->table)->where('id', $inventory->id)->update($data);
        
        // Create transaction record
        $this->create_transaction($inventory->id, 'return', $quantity, $order_id);
        
        return true;
    }

    /**
     * Confirm sale and deduct from inventory
     */
    function confirm_sale($item_id, $quantity, $order_id, $material_id = null, $warehouse_id = null) {
        // Get inventory record
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        if ($warehouse_id) {
            $where["warehouse_id"] = $warehouse_id;
        }
        
        $inventory = $this->get_one_where($where);
        
        if (!$inventory || $inventory->quantity_reserved < $quantity) {
            return false;
        }
        
        // Update quantities
        $data = array(
            'quantity_on_hand' => $inventory->quantity_on_hand - $quantity,
            'quantity_reserved' => $inventory->quantity_reserved - $quantity,
            'last_sale_date' => date('Y-m-d'),
            'updated_at' => get_current_utc_time()
        );
        
        $this->ci->db->table($this->table)->where('id', $inventory->id)->update($data);
        
        // Check for low stock
        $this->check_stock_levels($inventory->id);
        
        return true;
    }

    /**
     * Add stock (purchase/adjustment)
     */
    function add_stock($item_id, $quantity, $unit_cost = null, $reference_type = 'purchase', $reference_id = null, $material_id = null, $warehouse_id = null) {
        // Get or create inventory record
        $where = array("item_id" => $item_id, "deleted" => 0);
        
        if ($material_id) {
            $where["material_id"] = $material_id;
        }
        
        if ($warehouse_id) {
            $where["warehouse_id"] = $warehouse_id;
        } else {
            $where["warehouse_id"] = 1; // Default warehouse
        }
        
        $inventory = $this->get_one_where($where);
        
        if (!$inventory) {
            // Create new inventory record
            $data = array(
                'item_id' => $item_id,
                'material_id' => $material_id,
                'warehouse_id' => $warehouse_id ? $warehouse_id : 1,
                'quantity_on_hand' => $quantity,
                'quantity_available' => $quantity,
                'quantity_reserved' => 0,
                'unit_cost' => $unit_cost,
                'last_purchase_date' => date('Y-m-d'),
                'created_at' => get_current_utc_time()
            );
            
            $inventory_id = $this->ci->db->table($this->table)->insert($data);
        } else {
            // Update existing record
            $data = array(
                'quantity_on_hand' => $inventory->quantity_on_hand + $quantity,
                'quantity_available' => $inventory->quantity_available + $quantity,
                'last_purchase_date' => date('Y-m-d'),
                'updated_at' => get_current_utc_time()
            );
            
            if ($unit_cost !== null) {
                $data['unit_cost'] = $unit_cost;
            }
            
            $this->ci->db->table($this->table)->where('id', $inventory->id)->update($data);
            $inventory_id = $inventory->id;
        }
        
        // Create transaction record
        $this->create_transaction($inventory_id, $reference_type, $quantity, $reference_id, $unit_cost);
        
        return true;
    }

    /**
     * Create inventory transaction record
     */
    function create_transaction($inventory_id, $type, $quantity, $reference_id = null, $unit_cost = null) {
        $inventory = $this->get_one($inventory_id);
        
        $data = array(
            'inventory_id' => $inventory_id,
            'transaction_type' => $type,
            'reference_type' => $type == 'sale' ? 'order' : $type,
            'reference_id' => $reference_id,
            'quantity' => $quantity,
            'unit_cost' => $unit_cost,
            'total_cost' => $unit_cost ? $quantity * $unit_cost : null,
            'balance_before' => $inventory->quantity_on_hand,
            'balance_after' => $inventory->quantity_on_hand + ($type == 'purchase' || $type == 'return' ? $quantity : -$quantity),
            'created_by' => $this->login_user->id,
            'created_at' => get_current_utc_time()
        );
        
        return $this->ci->db->table('inventory_transactions')->insert($data);
    }

    /**
     * Check stock levels and create alerts
     */
    function check_stock_levels($inventory_id = null) {
        $where = array("deleted" => 0);
        
        if ($inventory_id) {
            $where["id"] = $inventory_id;
        }
        
        $inventories = $this->get_all_where($where)->getResult();
        
        foreach ($inventories as $inventory) {
            // Check for low stock
            if ($inventory->quantity_available <= $inventory->reorder_level && $inventory->reorder_level > 0) {
                $this->create_stock_alert($inventory->id, 'low_stock', $inventory->reorder_level, $inventory->quantity_available);
            }
            
            // Check for out of stock
            if ($inventory->quantity_available <= 0) {
                $this->create_stock_alert($inventory->id, 'out_of_stock', 0, $inventory->quantity_available);
            }
        }
    }

    /**
     * Create stock alert
     */
    function create_stock_alert($inventory_id, $alert_type, $threshold, $current_value) {
        // Check if alert already exists
        $existing = $this->ci->db->table('stock_alerts')
            ->where('inventory_id', $inventory_id)
            ->where('alert_type', $alert_type)
            ->where('status', 'active')
            ->get()
            ->getRow();
        
        if ($existing) {
            // Update existing alert
            $this->ci->db->table('stock_alerts')
                ->where('id', $existing->id)
                ->update(array(
                    'current_value' => $current_value,
                    'updated_at' => get_current_utc_time()
                ));
        } else {
            // Create new alert
            $data = array(
                'inventory_id' => $inventory_id,
                'alert_type' => $alert_type,
                'threshold_value' => $threshold,
                'current_value' => $current_value,
                'status' => 'active',
                'created_at' => get_current_utc_time()
            );
            
            $this->ci->db->table('stock_alerts')->insert($data);
        }
    }

    /**
     * Get active stock alerts
     */
    function get_active_alerts($warehouse_id = null) {
        $sql = "SELECT sa.*, i.item_id, i.material_id, i.warehouse_id, 
                       it.title as item_name, m.name as material_name, w.name as warehouse_name
                FROM stock_alerts sa
                JOIN inventory i ON sa.inventory_id = i.id
                JOIN items it ON i.item_id = it.id
                LEFT JOIN materials m ON i.material_id = m.id
                LEFT JOIN warehouses w ON i.warehouse_id = w.id
                WHERE sa.status = 'active' AND i.deleted = 0";
        
        if ($warehouse_id) {
            $sql .= " AND i.warehouse_id = " . $this->ci->db->escape($warehouse_id);
        }
        
        $sql .= " ORDER BY sa.created_at DESC";
        
        return $this->ci->db->query($sql)->getResult();
    }

    /**
     * Get inventory report
     */
    function get_inventory_report($filters = array()) {
        $sql = "SELECT i.*, it.title as item_name, it.unit_type, 
                       m.name as material_name, w.name as warehouse_name,
                       (i.quantity_on_hand * i.unit_cost) as total_value
                FROM inventory i
                JOIN items it ON i.item_id = it.id
                LEFT JOIN materials m ON i.material_id = m.id
                LEFT JOIN warehouses w ON i.warehouse_id = w.id
                WHERE i.deleted = 0";
        
        if (isset($filters['warehouse_id'])) {
            $sql .= " AND i.warehouse_id = " . $this->ci->db->escape($filters['warehouse_id']);
        }
        
        if (isset($filters['item_id'])) {
            $sql .= " AND i.item_id = " . $this->ci->db->escape($filters['item_id']);
        }
        
        if (isset($filters['low_stock_only']) && $filters['low_stock_only']) {
            $sql .= " AND i.quantity_available <= i.reorder_level AND i.reorder_level > 0";
        }
        
        $sql .= " ORDER BY it.title, m.name";
        
        return $this->ci->db->query($sql)->getResult();
    }
}