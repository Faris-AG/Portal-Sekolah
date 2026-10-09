<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

class CheckSeederData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-seeder-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi integritas data hasil dari Database Seeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa integritas data Seeder di Database...');

        $models = [
            ['nama' => 'Users (Total)', 'count' => User::count()],
            ['nama' => ' - Admin', 'count' => User::where('role', 'admin')->count()],
            ['nama' => ' - Guru', 'count' => User::where('role', 'guru')->count()],
            ['nama' => ' - Siswa', 'count' => User::where('role', 'siswa')->count()],
            ['nama' => 'School Classes', 'count' => SchoolClass::count()],
            ['nama' => 'Subjects', 'count' => Subject::count()],
            ['nama' => 'Schedules', 'count' => Schedule::count()],
            ['nama' => 'Materials', 'count' => Material::count()],
            ['nama' => 'Assignments', 'count' => Assignment::count()],
            ['nama' => 'Submissions', 'count' => Submission::count()],
            ['nama' => 'Attendance', 'count' => Attendance::count()],
            ['nama' => 'Announcements', 'count' => Announcement::count()],
            ['nama' => 'Announcement User (Pivot)', 'count' => DB::table('announcement_user')->count()],
        ];

        $headers = ['Nama Model / Tabel', 'Jumlah Baris Data', 'Status'];

        $rows = [];
        foreach ($models as $model) {
            $status = $model['count'] > 0 ? '✅ OK' : '❌ Kosong';
            
            // Special checks
            if ($model['nama'] == ' - Admin' && $model['count'] < 1) {
                $status = '❌ Butuh 1';
            } elseif ($model['nama'] == ' - Guru' && $model['count'] < 14) {
                $status = '❌ Butuh 14+';
            } elseif ($model['nama'] == ' - Siswa' && $model['count'] < 180) {
                $status = '❌ Butuh 180+';
            } elseif ($model['nama'] == 'School Classes' && $model['count'] < 6) {
                $status = '❌ Butuh 6';
            }
            
            $rows[] = [
                $model['nama'],
                $model['count'],
                $status
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Selesai melakukan verifikasi.');
    }
}
