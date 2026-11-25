<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 20); // 'mr', 'rlp', 'po', 'pr'
            $table->unsignedBigInteger('document_id');
            $table->unsignedTinyInteger('step'); // 1, 2, 3...
            $table->string('label', 100);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('signature')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });

        // Migrasikan tanda tangan yang sudah ada sebelumnya agar tidak hilang
        // 1. Material Requests
        if (Schema::hasTable('material_requests')) {
            $mrs = DB::table('material_requests')->get();
            foreach ($mrs as $mr) {
                $step = 1;
                if ($mr->approved_a_signature && $mr->approved_a_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'mr',
                        'document_id' => $mr->id,
                        'step' => $step++,
                        'label' => 'Approver 1',
                        'user_id' => $mr->approved_a_by,
                        'signature' => $mr->approved_a_signature,
                        'signed_at' => $mr->approved_a_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($mr->approved_c_signature && $mr->approved_c_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'mr',
                        'document_id' => $mr->id,
                        'step' => $step++,
                        'label' => 'Approver 2',
                        'user_id' => $mr->approved_c_by,
                        'signature' => $mr->approved_c_signature,
                        'signed_at' => $mr->approved_c_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($mr->paid_signature && $mr->paid_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'mr',
                        'document_id' => $mr->id,
                        'step' => $step++,
                        'label' => 'Finance',
                        'user_id' => $mr->paid_by,
                        'signature' => $mr->paid_signature,
                        'signed_at' => $mr->paid_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 2. RLPs (RRP)
        if (Schema::hasTable('rlps')) {
            $rlps = DB::table('rlps')->get();
            foreach ($rlps as $rlp) {
                $step = 1;
                if ($rlp->reviewed_signature && $rlp->reviewed_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'rlp',
                        'document_id' => $rlp->id,
                        'step' => $step++,
                        'label' => 'Review By',
                        'user_id' => $rlp->reviewed_by,
                        'signature' => $rlp->reviewed_signature,
                        'signed_at' => $rlp->reviewed_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($rlp->acknowledged_signature && $rlp->acknowledged_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'rlp',
                        'document_id' => $rlp->id,
                        'step' => $step++,
                        'label' => 'Acknowledge By',
                        'user_id' => $rlp->acknowledged_by,
                        'signature' => $rlp->acknowledged_signature,
                        'signed_at' => $rlp->acknowledged_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                if ($rlp->approved_signature && $rlp->approved_by) {
                    DB::table('document_approvals')->insert([
                        'document_type' => 'rlp',
                        'document_id' => $rlp->id,
                        'step' => $step++,
                        'label' => 'Approved By',
                        'user_id' => $rlp->approved_by,
                        'signature' => $rlp->approved_signature,
                        'signed_at' => $rlp->approved_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 3. Purchase Orders
        if (Schema::hasTable('purchase_order_approvals')) {
            $poApprovals = DB::table('purchase_order_approvals')->whereNotNull('user_id')->get();
            foreach ($poApprovals as $poa) {
                DB::table('document_approvals')->insert([
                    'document_type' => 'po',
                    'document_id' => $poa->purchase_order_id,
                    'step' => $poa->sort_order,
                    'label' => $poa->role_label,
                    'user_id' => $poa->user_id,
                    'signature' => $poa->signature,
                    'signed_at' => $poa->signed_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Purchase Requests
        if (Schema::hasTable('purchase_request_approvals')) {
            $prApprovals = DB::table('purchase_request_approvals')->whereNotNull('user_id')->get();
            foreach ($prApprovals as $pra) {
                DB::table('document_approvals')->insert([
                    'document_type' => 'pr',
                    'document_id' => $pra->purchase_request_id,
                    'step' => $pra->sort_order,
                    'label' => $pra->role_label,
                    'user_id' => $pra->user_id,
                    'signature' => $pra->signature,
                    'signed_at' => $pra->signed_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approvals');
    }
};
