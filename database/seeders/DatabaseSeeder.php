<?php

namespace Database\Seeders;

use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample data and default admin accounts.
     */
    public function run(): void
    {
        // 1. Groups (Roles)
        $adminGroup = DB::table('groups')->updateOrInsert(
            ['name' => 'admin'],
            ['description' => 'Administrator CBT']
        );
        $guruGroup = DB::table('groups')->updateOrInsert(
            ['name' => 'guru'],
            ['description' => 'Guru Pengampu & Pengawas']
        );
        $siswaGroup = DB::table('groups')->updateOrInsert(
            ['name' => 'siswa'],
            ['description' => 'Peserta Ujian']
        );

        $adminGroupId = DB::table('groups')->where('name', 'admin')->value('id') ?? 1;
        $guruGroupId  = DB::table('groups')->where('name', 'guru')->value('id') ?? 2;
        $siswaGroupId = DB::table('groups')->where('name', 'siswa')->value('id') ?? 3;

        // 2. Default Administrator Users
        // Admin 1: admin / admin123
        $admin1 = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'first_name' => 'Administrator CBT',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'email'      => 'admin@cbtlaravel.local',
                'active'     => 1,
                'created_on' => time(),
            ]
        );
        DB::table('users_groups')->updateOrInsert(
            ['user_id' => $admin1->id, 'group_id' => $adminGroupId]
        );

        // Admin 2: adminCBT / admin123
        $admin2 = User::updateOrCreate(
            ['username' => 'adminCBT'],
            [
                'first_name' => 'Wensislaus Wua (Admin CBT)',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'email'      => 'admincbt@cbtlaravel.local',
                'active'     => 1,
                'created_on' => time(),
            ]
        );
        DB::table('users_groups')->updateOrInsert(
            ['user_id' => $admin2->id, 'group_id' => $adminGroupId]
        );

        // 3. Default School Setting
        DB::table('setting')->updateOrInsert(
            ['id_setting' => 1],
            [
                'sekolah'       => 'SMA Negeri Benlutu',
                'jenjang'       => 3, // SMA
                'kepsek'        => 'Drs. Kepala Sekolah, M.Pd',
                'nip'           => '197001011995011001',
                'alamat'        => 'Jl. Raya Pendidikan No. 1',
                'kota'          => 'Timor Tengah Selatan',
                'provinsi'      => 'Nusa Tenggara Timur',
                'logo_kiri'     => 'uploads/settings/logo_kiri.png',
                'logo_kanan'    => 'uploads/settings/logo_kanan.png',
            ]
        );

        // 4. Default Academic Year & Semester
        $tp = MasterTp::firstOrCreate(
            ['tahun' => '2025/2026'],
            ['active' => 1]
        );
        $smt = MasterSmt::firstOrCreate(
            ['smt' => '2'],
            ['nama_smt' => 'Genap', 'active' => 1]
        );

        // 5. Default Teacher Account
        $userGuru = User::updateOrCreate(
            ['username' => 'guru'],
            [
                'first_name' => 'Budi Santoso, S.Pd',
                'password'   => password_hash('123456', PASSWORD_BCRYPT),
                'email'      => 'guru@cbtlaravel.local',
                'active'     => 1,
                'created_on' => time(),
            ]
        );
        DB::table('users_groups')->updateOrInsert(
            ['user_id' => $userGuru->id, 'group_id' => $guruGroupId]
        );
        DB::table('master_guru')->updateOrInsert(
            ['username' => 'guru'],
            [
                'id_user'      => $userGuru->id,
                'nama_guru'    => 'Budi Santoso, S.Pd',
                'nip'          => '198505102010011005',
                'status_aktif' => 1,
            ]
        );

        // 6. Default Student Account
        $userSiswa = User::updateOrCreate(
            ['username' => 'siswa'],
            [
                'first_name' => 'Ahmad Rizky',
                'password'   => password_hash('123456', PASSWORD_BCRYPT),
                'email'      => 'siswa@cbtlaravel.local',
                'active'     => 1,
                'created_on' => time(),
            ]
        );
        DB::table('users_groups')->updateOrInsert(
            ['user_id' => $userSiswa->id, 'group_id' => $siswaGroupId]
        );
        DB::table('master_siswa')->updateOrInsert(
            ['username' => 'siswa'],
            [
                'nama'         => 'Ahmad Rizky',
                'nis'          => '1001',
                'nisn'         => '0051234567',
                'password'     => '123456',
                'kelas_awal'   => '1',
                'nik'          => '3201010101010001',
                'warga_negara' => 'Indonesia',
                'uid'          => (string) Str::uuid(),
            ]
        );

        $this->command->info("Default Users & Sample Data Seeded Successfully!");
        $this->command->info("------------------------------------------------");
        $this->command->info("Admin 1 : Username: admin    | Password: admin123");
        $this->command->info("Admin 2 : Username: adminCBT | Password: admin123");
        $this->command->info("Guru    : Username: guru     | Password: 123456");
        $this->command->info("Siswa   : Username: siswa    | Password: 123456");
        $this->command->info("------------------------------------------------");
    }
}
