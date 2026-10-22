<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Submission;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Superadmin & Admin Accounts
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@wastingmoney.id'],
            [
                'name' => 'superadmin',
                'password' => Hash::make('admin123'),
                'role' => 'superadmin',
                'firebase_uid' => 'fb_superadmin_001',
            ]
        );

        User::updateOrCreate(
            ['email' => 'sysadmin@wastingmoney.id'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'firebase_uid' => 'fb_admin_sys_002',
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@wastingmoney.id'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'firebase_uid' => 'fb_admin_sys_001',
            ]
        );

        // 2. User Accounts
        $user1 = User::firstOrCreate(
            ['email' => 'user@wastingmoney.id'],
            [
                'name' => 'user',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'firebase_uid' => 'fb_user_dimas_001',
            ]
        );

        $userFigma = User::firstOrCreate(
            ['email' => 'uccloud26@gmail.com'],
            [
                'name' => 'uccloud26',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'firebase_uid' => 'fb_user_uccloud_002',
            ]
        );

        $userMitra = User::firstOrCreate(
            ['email' => 'siti.rahmayanti@mitra.org'],
            [
                'name' => 'siti',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'firebase_uid' => 'fb_user_siti_003',
            ]
        );

        // 3. Merch Submissions matching Figma mockup / Admin table
        $subs = [
            [
                'user_id' => $userFigma->id,
                'user_name' => 'uccloud26',
                'user_email' => 'uccloud26@gmail.com',
                'request_id' => '0000001',
                'title' => 'Taking Back Sunday',
                'artist_band' => 'Taking Back Sunday',
                'merch_type' => 'Vinyl',
                'released_year' => '2009',
                'merch_edition' => 'First Press Limited Edition',
                'category' => 'Vinyl',
                'description' => 'Taking Back Sunday New Again 2009 LP Vinyl First Pressing gatefold cover in mint condition.',
                'contact' => 'uccloud26@gmail.com',
                'file_name' => 'img-01.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=600&auto=format&fit=crop&q=80',
                'status' => 'In Progress',
                'admin_notes' => 'Sedang diverifikasi ketersediaan dan keaslian barang.',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $user1->id,
                'user_name' => 'user',
                'user_email' => 'user@wastingmoney.id',
                'request_id' => '0000002',
                'title' => 'Taylor Swift',
                'artist_band' => 'Taylor Swift',
                'merch_type' => 'T-Shirt',
                'released_year' => '2024',
                'merch_edition' => 'The Eras Tour North America',
                'category' => 'T-Shirt',
                'description' => 'Taylor Swift North American Tour 2024, Long Sleeve, White with complete tour list.',
                'contact' => 'user@wastingmoney.id',
                'file_name' => 'img-02.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                'status' => 'Approved',
                'admin_notes' => 'Disetujui. Merchandise terverifikasi resmi.',
                'approved_at' => now()->subDay(),
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $userMitra->id,
                'user_name' => 'siti',
                'user_email' => 'siti.rahmayanti@mitra.org',
                'request_id' => '0000003',
                'title' => 'Radiohead',
                'artist_band' => 'Radiohead',
                'merch_type' => 'Hoodie',
                'released_year' => '2020',
                'merch_edition' => 'Kid A Mnesia Commemorative',
                'category' => 'Hoodie',
                'description' => 'Official Radiohead heavyweight cotton black hoodie with red screen printed artwork.',
                'contact' => 'siti.rahmayanti@mitra.org',
                'file_name' => 'img-03.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600&auto=format&fit=crop&q=80',
                'status' => 'In Progress',
                'admin_notes' => 'Dalam proses pengecekan stockist partner.',
                'created_at' => now()->subHours(12),
            ],
            [
                'user_id' => $userFigma->id,
                'user_name' => 'uccloud26',
                'user_email' => 'uccloud26@gmail.com',
                'request_id' => '0000004',
                'title' => 'Deftones',
                'artist_band' => 'Deftones',
                'merch_type' => 'Vinyl',
                'released_year' => '2016',
                'merch_edition' => 'Gore Tour Edition',
                'category' => 'Vinyl',
                'description' => 'Deftones Gore Double LP with flamingos artwork gatefold sleeve.',
                'contact' => 'uccloud26@gmail.com',
                'file_name' => 'img-04.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&auto=format&fit=crop&q=80',
                'status' => 'Pending',
                'admin_notes' => null,
                'created_at' => now()->subHours(4),
            ],
            [
                'user_id' => $user1->id,
                'user_name' => 'user',
                'user_email' => 'user@wastingmoney.id',
                'request_id' => '0000005',
                'title' => 'The 1975',
                'artist_band' => 'The 1975',
                'merch_type' => 'Poster',
                'released_year' => '2023',
                'merch_edition' => 'At Their Very Best World Tour',
                'category' => 'Poster',
                'description' => 'Limited screenprinted silk poster from London show date, hand numbered.',
                'contact' => 'user@wastingmoney.id',
                'file_name' => 'img-05.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?w=600&auto=format&fit=crop&q=80',
                'status' => 'Approved',
                'admin_notes' => 'Disetujui. Tayang di katalog barang verified.',
                'approved_at' => now()->subHours(2),
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($subs as $subData) {
            $createdSub = Submission::create($subData);

            // Jika status approved, masukkan ke katalog publik
            if (strtolower($createdSub->status) === 'approved') {
                Item::create([
                    'submission_id' => $createdSub->id,
                    'title' => $createdSub->artist_band . ' - ' . ($createdSub->merch_edition ?: $createdSub->merch_type),
                    'slug' => Str::slug($createdSub->artist_band . '-' . $createdSub->merch_type . '-' . $createdSub->id),
                    'category' => $createdSub->merch_type,
                    'description' => $createdSub->description,
                    'contact' => $createdSub->contact,
                    'image_url' => $createdSub->image_url,
                    'views' => rand(12, 85),
                ]);
            }
        }

        // Notification alert
        Notification::create([
            'type' => 'new_submission',
            'title' => 'Request Merch Masuk: Taking Back Sunday',
            'message' => 'Pengajuan request merchandise vinyl dari uccloud26 siap ditinjau oleh Administrator.',
            'payload' => ['request_id' => '0000001', 'title' => 'Taking Back Sunday', 'status' => 'In Progress'],
            'is_read' => false,
        ]);
    }
}
