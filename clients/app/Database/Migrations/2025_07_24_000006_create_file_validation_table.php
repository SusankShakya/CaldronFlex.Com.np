<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFileValidationTable extends Migration
{
    public function up()
    {
        // File validation rules table
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
                'null' => false,
            ],
            'file_category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'design, print_ready, proof, etc.',
            ],
            'allowed_extensions' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => 'JSON array of allowed file extensions',
            ],
            'max_file_size_mb' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 100,
            ],
            'min_resolution_dpi' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'max_resolution_dpi' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'color_mode_requirements' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of required color modes',
            ],
            'dimension_requirements' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object with min/max width and height',
            ],
            'validation_rules' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object with custom validation rules',
            ],
            'error_messages' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object with custom error messages',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'priority' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Higher priority rules are checked first',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('file_category');
        $this->forge->addKey('is_active');
        $this->forge->createTable('file_validation_rules');

        // File validation logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'file_size' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
            ],
            'file_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'validation_rule_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'validation_status' => [
                'type' => 'ENUM',
                'constraint' => ['passed', 'failed', 'warning'],
                'null' => false,
            ],
            'validation_errors' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of validation errors',
            ],
            'validation_warnings' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of validation warnings',
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object with file metadata',
            ],
            'reference_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'order, estimate, item, etc.',
            ],
            'reference_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'uploaded_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('validation_rule_id');
        $this->forge->addKey('validation_status');
        $this->forge->addKey(['reference_type', 'reference_id']);
        $this->forge->createTable('file_validation_logs');

        // Insert default validation rules
        $this->db->table('file_validation_rules')->insertBatch([
            [
                'rule_name' => 'Design Files - Vector',
                'file_category' => 'design',
                'allowed_extensions' => json_encode(['ai', 'eps', 'svg', 'pdf', 'cdr']),
                'max_file_size_mb' => 500,
                'validation_rules' => json_encode([
                    'check_vector_format' => true,
                    'check_fonts_embedded' => true,
                    'check_color_mode' => true
                ]),
                'error_messages' => json_encode([
                    'file_type' => 'Please upload a vector file (AI, EPS, SVG, PDF, or CDR)',
                    'file_size' => 'File size must not exceed 500MB',
                    'vector_format' => 'File must be in vector format',
                    'fonts_embedded' => 'All fonts must be embedded or converted to outlines'
                ]),
                'is_active' => 1,
                'priority' => 10,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'rule_name' => 'Design Files - Raster',
                'file_category' => 'design',
                'allowed_extensions' => json_encode(['psd', 'tiff', 'tif', 'png', 'jpg', 'jpeg']),
                'max_file_size_mb' => 1000,
                'min_resolution_dpi' => 150,
                'color_mode_requirements' => json_encode(['CMYK', 'RGB']),
                'validation_rules' => json_encode([
                    'check_resolution' => true,
                    'check_color_mode' => true,
                    'check_dimensions' => true
                ]),
                'error_messages' => json_encode([
                    'file_type' => 'Please upload a high-resolution image file',
                    'file_size' => 'File size must not exceed 1GB',
                    'resolution' => 'Image resolution must be at least 150 DPI',
                    'color_mode' => 'Image must be in CMYK or RGB color mode'
                ]),
                'is_active' => 1,
                'priority' => 5,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'rule_name' => 'Print Ready Files',
                'file_category' => 'print_ready',
                'allowed_extensions' => json_encode(['pdf']),
                'max_file_size_mb' => 2000,
                'min_resolution_dpi' => 300,
                'color_mode_requirements' => json_encode(['CMYK']),
                'validation_rules' => json_encode([
                    'check_pdf_version' => true,
                    'check_bleed' => true,
                    'check_fonts_embedded' => true,
                    'check_resolution' => true,
                    'check_color_mode' => true
                ]),
                'error_messages' => json_encode([
                    'file_type' => 'Print ready files must be in PDF format',
                    'file_size' => 'File size must not exceed 2GB',
                    'resolution' => 'All images must be at least 300 DPI',
                    'color_mode' => 'File must use CMYK color mode',
                    'bleed' => 'File must include proper bleed (0.125 inch minimum)'
                ]),
                'is_active' => 1,
                'priority' => 20,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('file_validation_logs');
        $this->forge->dropTable('file_validation_rules');
    }
}