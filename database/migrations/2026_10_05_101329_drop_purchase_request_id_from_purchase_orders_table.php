   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up(): void
       {
           // Relasi PO -> PR sekarang lewat purchase_requests.purchase_order_id,
           // jadi kolom lama di purchase_orders sudah tidak dipakai.
           if (Schema::hasColumn('purchase_orders', 'purchase_request_id')) {
               Schema::table('purchase_orders', function (Blueprint $table) {
                   $table->dropConstrainedForeignId('purchase_request_id');
               });
           }
       }

       public function down(): void
       {
           if (! Schema::hasColumn('purchase_orders', 'purchase_request_id')) {
               Schema::table('purchase_orders', function (Blueprint $table) {
                   $table->foreignId('purchase_request_id')->nullable()
                       ->after('rlp_id')
                       ->constrained('purchase_requests')
                       ->nullOnDelete();
               });
           }
       }
   };
