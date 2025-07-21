<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClearContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('article_category')->delete();
        \DB::table('articles')->delete();
        \DB::table('categories')->delete();

        echo "All articles and categories deleted successfully.\n";
    }
}
