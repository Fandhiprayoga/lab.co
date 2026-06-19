<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificateTemplateComponentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'component_type' => [
                'type'       => 'ENUM',
                'constraint' => ['recipient_name', 'cert_number', 'issued_date', 'title', 'custom_text', 'logo'],
                'default'    => 'custom_text',
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'x_position' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'y_position' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'font_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 16,
            ],
            'font_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '#000000',
            ],
            'font_family' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Arial, sans-serif',
            ],
            'font_weight' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'bold'],
                'default'    => 'normal',
            ],
            'text_align' => [
                'type'       => 'ENUM',
                'constraint' => ['left', 'center', 'right'],
                'default'    => 'center',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('template_id');
        $this->forge->addForeignKey('template_id', 'certificate_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('certificate_template_components', true);
    }

    public function down()
    {
        $this->forge->dropTable('certificate_template_components', true);
    }
}
