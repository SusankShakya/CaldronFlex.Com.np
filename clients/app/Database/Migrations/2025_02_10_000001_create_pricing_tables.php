<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePricingTables extends Migration
{
    public function up()
    {
        // Create pricing_rules table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'rule_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'rule_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'area_based, quantity_tier, custom, customer_specific',
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'NULL for global rules',
            ],
            'category_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Apply to entire category',
            ],
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'For customer-specific pricing',
            ],
            'min_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'max_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'NULL for unlimited',
            ],
            'min_area' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Min area in sq ft',
            ],
            'max_area' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Max area in sq ft',
            ],
            'base_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Base rate per sq ft or fixed price',
            ],
            'discount_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'percentage',
                'comment' => 'percentage or fixed',
            ],
            'discount_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'priority' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Higher priority rules apply first',
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('rule_type');
        $this->forge->addKey('status');
        $this->forge->createTable('pricing_rules');

        // Create custom_quotes table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'quote_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'item_description' => [
                'type' => 'TEXT',
                'comment' => 'Detailed description of custom requirements',
            ],
            'dimensions' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON of width, height, depth, etc.',
            ],
            'variant_combination' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of selected variant IDs',
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'discount_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'pricing_breakdown' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON of price calculation details',
            ],
            'valid_until' => [
                'type' => 'DATE',
            ],
            'quote_status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'draft',
                'comment' => 'draft, sent, accepted, rejected, expired, converted',
            ],
            'converted_to_order_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('quote_number');
        $this->forge->addKey('client_id');
        $this->forge->addKey('item_id');
        $this->forge->addKey('quote_status');
        $this->forge->createTable('custom_quotes');

        // Create pricing_calculations_log table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'quote_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'order_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'calculation_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'real_time, quote, order',
            ],
            'input_parameters' => [
                'type' => 'TEXT',
                'comment' => 'JSON of input data (dimensions, variants, quantity)',
            ],
            'applied_rules' => [
                'type' => 'TEXT',
                'comment' => 'JSON array of pricing rule IDs applied',
            ],
            'base_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'variant_modifiers' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'quantity_discount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'customer_discount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'final_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'calculation_details' => [
                'type' => 'TEXT',
                'comment' => 'Full JSON breakdown of calculation steps',
            ],
            'cache_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'For caching calculations',
            ],
            'calculated_at' => [
                'type' => 'DATETIME',
            ],
            'calculated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('client_id');
        $this->forge->addKey('quote_id');
        $this->forge->addKey('order_id');
        $this->forge->addKey('calculation_type');
        $this->forge->addKey('cache_key');
        $this->forge->addKey('calculated_at');
        $this->forge->createTable('pricing_calculations_log');

        // Add pricing_enabled field to items table
        $fields = [
            'pricing_type' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'fixed',
                'comment' => 'fixed, area_based, custom',
                'after' => 'rate',
            ],
            'enable_area_pricing' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Enable area-based pricing',
                'after' => 'pricing_type',
            ],
            'min_order_area' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Minimum area in sq ft',
                'after' => 'enable_area_pricing',
            ],
            'max_order_area' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Maximum area in sq ft',
                'after' => 'min_order_area',
            ],
        ];
        $this->forge->addColumn('items', $fields);
    }

    public function down()
    {
        // Remove added columns from items table
        $this->forge->dropColumn('items', ['pricing_type', 'enable_area_pricing', 'min_order_area', 'max_order_area']);
        
        // Drop tables
        $this->forge->dropTable('pricing_calculations_log', true);
        $this->forge->dropTable('custom_quotes', true);
        $this->forge->dropTable('pricing_rules', true);
    }
}