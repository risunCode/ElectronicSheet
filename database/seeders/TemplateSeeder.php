<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Blank Document',
                'slug' => 'blank-document',
                'description' => 'Empty document to start from scratch',
                'type' => 'blank',
                'is_system' => true,
                'content' => [
                    'title' => '',
                    'content' => '',
                ],
            ],
            [
                'name' => 'Surat Resmi',
                'slug' => 'surat-resmi',
                'description' => 'Template surat resmi/formal',
                'type' => 'docx',
                'is_system' => true,
                'content' => [
                    'title' => 'Surat',
                    'content' => '<p style="text-align: right;">Jakarta, [Tanggal]</p><p><br></p><p>Kepada Yth,</p><p>[Nama Penerima]</p><p>[Alamat]</p><p><br></p><p>Dengan hormat,</p><p><br></p><p>[Isi surat]</p><p><br></p><p style="text-align: left;">Hormat kami,</p><p><br></p><p>[Nama Pengirim]</p>',
                ],
            ],
            [
                'name' => 'Laporan',
                'slug' => 'laporan',
                'description' => 'Template laporan standar',
                'type' => 'docx',
                'is_system' => true,
                'content' => [
                    'title' => 'Laporan',
                    'content' => '<h1 style="text-align: center;">LAPORAN</h1><p style="text-align: center;">[Judul Laporan]</p><p><br></p><h2>BAB I - PENDAHULUAN</h2><p>[Isi pendahuluan]</p><p><br></p><h2>BAB II - PEMBAHASAN</h2><p>[Isi pembahasan]</p><p><br></p><h2>BAB III - PENUTUP</h2><p>[Kesimpulan]</p>',
                ],
            ],
            [
                'name' => 'Notulen Rapat',
                'slug' => 'notulen-rapat',
                'description' => 'Template notulen/minutes of meeting',
                'type' => 'docx',
                'is_system' => true,
                'content' => [
                    'title' => 'Notulen Rapat',
                    'content' => '<h1 style="text-align: center;">NOTULEN RAPAT</h1><p><br></p><table><tr><td>Hari/Tanggal</td><td>: [Tanggal]</td></tr><tr><td>Waktu</td><td>: [Waktu]</td></tr><tr><td>Tempat</td><td>: [Lokasi]</td></tr><tr><td>Agenda</td><td>: [Agenda Rapat]</td></tr></table><p><br></p><h2>Peserta Rapat:</h2><ol><li>[Nama 1]</li><li>[Nama 2]</li></ol><p><br></p><h2>Pembahasan:</h2><p>[Isi pembahasan]</p><p><br></p><h2>Keputusan:</h2><ol><li>[Keputusan 1]</li></ol>',
                ],
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
