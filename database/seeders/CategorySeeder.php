<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->delete();
        $categories = [
             [
                'id' => 3,
                'name' => 'Tiểu thuyết & Văn học',
                'description' => 'Nơi tập hợp những tác phẩm kinh điển và hiện đại, từ tiểu thuyết trinh thám, phiêu lưu, đến các tác phẩm văn học kinh điển của thế giới.',
            ],
            [
                'id' => 5,
                'name' => 'Công nghệ & Khoa học',
                'description' => 'Sách về lập trình, công nghệ thông tin, phát triển phần mềm, khoa học vũ trụ, và những kiến thức giúp bạn khám phá thế giới hiện đại.',
            ],
            [
                'id' => 6,
                'name' => 'Lịch sử & Nhân loại học',
                'description' => 'Những cuốn sách giúp bạn tìm hiểu lịch sử phát triển của loài người, các nền văn minh và những thay đổi đã làm nên thế giới ngày nay.',
            ],
            [
                'id' => 7,
                'name' => 'Kinh doanh & Phát triển bản thân',
                'description' => 'Các đầu sách truyền cảm hứng, phát triển tư duy, khởi nghiệp và quản trị, giúp bạn đạt được thành công trong công việc và cuộc sống.',
            ],
            [
                'id' => 8,
                'name' => 'Sách Thiếu nhi',
                'description' => 'Những tác phẩm văn học và truyện tranh phù hợp cho trẻ em, nuôi dưỡng trí tưởng tượng và giáo dục nhân cách.',
            ],
            [
                'id' => 9,
                'name' => 'Tâm lý & Kỹ năng sống',
                'description' => 'Bộ sưu tập sách giúp bạn rèn luyện kỹ năng giao tiếp, thấu hiểu bản thân, và tạo động lực trong cuộc sống.',
            ],
        ];
        DB::table('categories')->insert($categories);
    }
}
