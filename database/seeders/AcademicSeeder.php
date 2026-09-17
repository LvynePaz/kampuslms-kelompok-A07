<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dosen 
        $dosenDemo = User::where('email', 'dosen@kampuslms.test')->first();

        $lecturersData = [
            [
                'name' => 'Hendy Indrawan Sunardi, S.Kom., M.Eng.',
                'email' => 'hendy@kampuslms.test',
                'nim_nip' => '198501012010121001',
            ],
            [
                'name' => 'Vika Fitratunnany Insanittaqwa, S.Kom., M.Kom.',
                'email' => 'vika@kampuslms.test',
                'nim_nip' => '198802022012122001',
            ],
            [
                'name' => 'Yuyun Tri Wiranti, S.Kom., M.MT',
                'email' => 'yuyun@kampuslms.test',
                'nim_nip' => '198203032008122001',
            ],
            [
                'name' => 'Arif Wicaksono Septyanto, M.Kom',
                'email' => 'arif@kampuslms.test',
                'nim_nip' => '198604042011121001',
            ],
            [
                'name' => 'Ir. I Putu Deny Arthawan Sugih Prabowo, M.Eng.',
                'email' => 'putu@kampuslms.test',
                'nim_nip' => '197905052005121001',
            ],
        ];

        $lecturers = [];
        if ($dosenDemo) {
            $lecturers[] = $dosenDemo;
        }

        foreach ($lecturersData as $data) {
            $lecturer = User::firstOrNew(['email' => $data['email']]);
            $lecturer->name = $data['name'];
            $lecturer->password = Hash::make('password');
            $lecturer->role = 'dosen';
            $lecturer->nim_nip = $data['nim_nip'];
            $lecturer->email_verified_at = now();
            $lecturer->save();
            $lecturers[] = $lecturer;
        }

        // 2. Mahasiswa 35 mahasiswa 1 nya akun demo 
        $mahasiswaDemo = User::where('email', 'mahasiswa@kampuslms.test')->first();

        $students = [];
        if ($mahasiswaDemo) {
            $students[] = $mahasiswaDemo;
        }

        $studentNames = [
            'Ahmad Fauzi', 'Budi Santoso', 'Citra Dewi', 'Dimas Pratama',
            'Eka Saputra', 'Fani Rahmawati', 'Gilang Ramadhan', 'Hana Pertiwi',
            'Indra Lesmana', 'Joko Susilo', 'Kartika Sari', 'Lukman Hakim',
            'Maya Anggraini', 'Naufal Izzudin', 'Olivia Putri', 'Panji Gumilang',
            'Qori Annisa', 'Rian Hidayat', 'Siti Nurhaliza', 'Teguh Wibowo',
            'Umar Dani', 'Vina Melati', 'Wahyu Setiawan', 'Xaverius Yoga',
            'Yogi Prasetyo', 'Zahra Amelia', 'Aditya Nugraha', 'Bella Safitri',
            'Candra Wijaya', 'Dina Lestari', 'Fajar Ramli', 'Gita Gutawa',
            'Haris Munandar', 'Intan Permata',
        ];

        foreach ($studentNames as $idx => $name) {
            $nim = '102410' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
            $email = 'mhs' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT) . '@kampuslms.test';

            $student = User::firstOrNew(['email' => $email]);
            $student->name = $name;
            $student->password = Hash::make('password');
            $student->role = 'mahasiswa';
            $student->nim_nip = $nim;
            $student->email_verified_at = now();
            $student->save();
            $students[] = $student;
        }

        // 3. Mata Kuliah (Sesuai Jadwal Kuliah)
        $coursesData = [
            [
                'code' => 'SI2514021',
                'name' => 'Perencanaan Arsitektur Teknologi Informasi',
                'description' => 'Membahas penyusunan kerangka kerja arsitektur enterprise TI (TOGAF/Zachman) untuk menyelaraskan teknologi dengan strategi bisnis organisasi.',
                'sks' => 3,
                'lecturer_id' => $lecturers[1]->id, // pak Hendy
                'status' => 'active',
            ],
            [
                'code' => 'SI2514022',
                'name' => 'Manajemen Sumber Daya Manusia',
                'description' => 'Membahas pengelolaan SDM, talent management, motivasi kerja, dinamika tim, dan kepemimpinan di era transformasi digital.',
                'sks' => 2,
                'lecturer_id' => $lecturers[2]->id, // bu Vika
                'status' => 'active',
            ],
            [
                'code' => 'SI2514023',
                'name' => 'Perencanaan Strategis Sistem Informasi',
                'description' => 'Membekali metodologi penyusunan rencana strategis sistem informasi dan teknologi informasi (Renstra SI/TI) menggunakan framework Ward & Peppard.',
                'sks' => 3,
                'lecturer_id' => $lecturers[3]->id, // bu Yuyun
                'status' => 'active',
            ],
            [
                'code' => 'SI2514024',
                'name' => 'Kecerdasan Bisnis A',
                'description' => 'Membahas teknik ekstraksi data, data warehousing, OLAP, dashboarding, dan analisis data untuk mendukung pengambilan keputusan bisnis.',
                'sks' => 3,
                'lecturer_id' => $lecturers[4]->id, // pak Arif 
                'status' => 'active',
            ],
            [
                'code' => 'SI2514025',
                'name' => 'Perencanaan Keberlangsungan Bisnis B',
                'description' => 'Mempelajari Business Impact Analysis (BIA), mitigasi risiko bencana TI, Disaster Recovery Planning (DRP), dan Business Continuity Plan (BCP).',
                'sks' => 3,
                'lecturer_id' => $lecturers[5]->id, // pak Deny 
                'status' => 'active',
            ],
            [
                'code' => 'SI2514026',
                'name' => 'Perancangan dan Pengembangan Perangkat Lunak',
                'description' => 'Mempelajari siklus hidup pengembangan perangkat lunak (SDLC), pemodelan UML, arsitektur MVC, dan implementasi modern berbasis web framework.',
                'sks' => 3,
                'lecturer_id' => $dosenDemo ? $dosenDemo->id : $lecturers[0]->id, // Dosen Demo
                'status' => 'active',
            ],
        ];

        $courses = [];
        foreach ($coursesData as $cData) {
            $course = Course::updateOrCreate(
                ['code' => $cData['code']],
                $cData
            );
            $courses[] = $course;
        }

        // 4. Enrollment Mahasiswa (Setiap MK minimal 18-22 mahasiswa terdaftar)
        foreach ($courses as $course) {
            // Pastikan mahasiswa demo selalu terdaftar di setiap mata kuliah
            $enrolledStudentIds = [$students[0]->id];

            // Tambahkan 18 mahasiswa acak lainnya agar tiap MK punya ≥ 19 mahasiswa
            $otherStudents = array_slice($students, 1);
            shuffle($otherStudents);
            $selected = array_slice($otherStudents, 0, 18);

            foreach ($selected as $st) {
                $enrolledStudentIds[] = $st->id;
            }

            $attachData = [];
            foreach ($enrolledStudentIds as $sId) {
                $attachData[$sId] = [
                    'enrolled_at' => now()->subDays(rand(14, 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $course->students()->syncWithoutDetaching($attachData);
        }

        // 5. Materi Kuliah (Materials)
        foreach ($courses as $course) {
            Material::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => 'Kontrak Perkuliahan & RPS ' . $course->name,
                ],
                [
                    'uploaded_by' => $course->lecturer_id,
                    'description' => 'Silabus dan panduan perkuliahan selama 1 semester.',
                    'type' => 'file',
                    'file_path' => 'materials/' . strtolower($course->code) . '-rps.pdf',
                    'original_name' => 'RPS-' . $course->code . '.pdf',
                    'file_size' => 524288,
                    'mime_type' => 'application/pdf',
                ]
            );

            Material::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => 'Pengenalan dan Konsep Dasar ' . $course->name,
                ],
                [
                    'uploaded_by' => $course->lecturer_id,
                    'description' => 'Materi pengantar modul minggu ke-1.',
                    'type' => 'link',
                    'external_url' => 'https://kampuslms.test/materials/' . strtolower($course->code) . '/materi-1',
                ]
            );
        }

        // 6. Tugas (3 tugas per MK: lewat deadline, aktif, draft)
        $publishedAssignments = [];

        foreach ($courses as $course) {
            // Tugas 1: Sudah lewat deadline (past)
            $tugas1 = Assignment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => 'Tugas 1: Studi Kasus dan Analisis Permasalahan',
                ],
                [
                    'created_by' => $course->lecturer_id,
                    'instructions' => 'Analisis kasus yang diberikan pada modul minggu ke-2, buat laporan dalam format PDF maksimal 5 halaman.',
                    'due_at' => now()->subDays(rand(3, 10)),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ]
            );
            $publishedAssignments[] = $tugas1;

            // Tugas 2: Aktif / Mendatang (active)
            $tugas2 = Assignment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => 'Tugas 2: Desain Rancangan dan Kerangka Kerja',
                ],
                [
                    'created_by' => $course->lecturer_id,
                    'instructions' => 'Buat diagram arsitektur / pemodelan sesuai topik mata kuliah dan lampirkan penjelasan detailnya.',
                    'due_at' => now()->addDays(rand(4, 14)),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ]
            );
            $publishedAssignments[] = $tugas2;

            // Tugas 3: Draft
            Assignment::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => 'Tugas 3: Laporan Akhir Proyek Semester',
                ],
                [
                    'created_by' => $course->lecturer_id,
                    'instructions' => 'Draft tugas proyek akhir kelompok (belum dipublikasikan ke mahasiswa).',
                    'due_at' => now()->addDays(30),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ]
            );
        }

        // 7. Submissions (≥ 100 submission) & Grades (~60% dinilai)
        $feedbacks = [
            'Analisis sangat tajam dan penyusunan dokumen sangat terstruktur. Pertahankan.',
            'Kerja bagus, diagram lengkap dan mudah dipahami.',
            'Sudah memenuhi seluruh kriteria penugasan, referensi cukup relevan.',
            'Analisis baik, namun perlu pendalaman pada aspek mitigasi risiko.',
            'Dokumen rapi, penjelasan metode perancangan cukup detail.',
            'Cukup baik, tetapi penulisan sitasi dan daftar pustaka perlu diseragamkan.',
        ];

        $totalSubmissions = 0;
        $gradedCount = 0;

        foreach ($publishedAssignments as $assignment) {
            $course = $assignment->course;
            $enrolledStudents = $course->students;

            // Di tugas yang lewat deadline (Tugas 1), 12-14 mahasiswa submit
            // Di tugas aktif (Tugas 2), 6-8 mahasiswa submit duluan
            $isPast = $assignment->due_at->isPast();
            $submitCount = $isPast ? rand(12, 14) : rand(6, 8);

            $submitters = $enrolledStudents->take($submitCount);

            foreach ($submitters as $student) {
                $submittedAt = $isPast
                    ? $assignment->due_at->copy()->subHours(rand(2, 48))
                    : now()->subDays(rand(1, 3));

                $submission = Submission::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'file_path' => 'submissions/' . strtolower($course->code) . '-' . $student->id . '.pdf',
                        'original_name' => 'Tugas-' . str_replace(' ', '_', $student->name) . '.pdf',
                        'file_size' => rand(150000, 2500000),
                        'note' => rand(0, 1) ? 'Berikut lampiran tugas saya, terima kasih.' : null,
                        'submitted_at' => $submittedAt,
                        'is_late' => false,
                    ]
                );

                $totalSubmissions++;

                // Sekitar 60% submission dinilai (terutama yang tugas lewat deadline)
                if ($isPast && rand(1, 10) <= 7) {
                    Grade::updateOrCreate(
                        ['submission_id' => $submission->id],
                        [
                            'graded_by' => $course->lecturer_id,
                            'score' => rand(75, 98),
                            'feedback' => $feedbacks[array_rand($feedbacks)],
                            'graded_at' => $submittedAt->copy()->addDays(rand(1, 3)),
                        ]
                    );
                    $gradedCount++;
                }
            }
        }
    }
}
