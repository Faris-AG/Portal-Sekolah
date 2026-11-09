<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Optimization Migration
 *
 * Menambahkan database index pada kolom-kolom yang sering digunakan
 * untuk filter, ordering, dan JOIN agar query lebih cepat (anti slow query).
 *
 * Kolom yang sudah memiliki index otomatis dari Eloquent TIDAK diindeks ulang:
 *  - foreignId() secara implisit menambahkan index di sebagian database.
 *  - email sudah unique() di tabel users.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel users ────────────────────────────────────────────
        // `role` sering di-filter (WHERE role = 'siswa', WHERE role = 'guru')
        // `email_verified_at` sering di-filter (whereNotNull / whereNull)
        // `class_id`  sering di-filter (WHERE class_id = ?)
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
            if (!$this->hasIndex('users', 'users_email_verified_at_index')) {
                $table->index('email_verified_at', 'users_email_verified_at_index');
            }
            if (!$this->hasIndex('users', 'users_class_id_index')) {
                $table->index('class_id', 'users_class_id_index');
            }
        });

        // ── Tabel attendances ───────────────────────────────────────
        // `status`           di-filter (WHERE status = 'hadir', dll.)
        // `date`             di-filter (WHERE date LIKE 'Y-m%', BETWEEN)
        // Composite (student_id, date) untuk query rekap per siswa/bulan
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->hasIndex('attendances', 'attendances_status_index')) {
                $table->index('status', 'attendances_status_index');
            }
            if (!$this->hasIndex('attendances', 'attendances_date_index')) {
                $table->index('date', 'attendances_date_index');
            }
            if (!$this->hasIndex('attendances', 'attendances_student_date_index')) {
                $table->index(['student_id', 'date'], 'attendances_student_date_index');
            }
        });

        // ── Tabel materials ─────────────────────────────────────────
        // `created_at` untuk ordering (latest())
        // Composite (teacher_id, school_class_id) untuk filter gabungan
        Schema::table('materials', function (Blueprint $table) {
            if (!$this->hasIndex('materials', 'materials_created_at_index')) {
                $table->index('created_at', 'materials_created_at_index');
            }
            if (!$this->hasIndex('materials', 'materials_teacher_class_index')) {
                $table->index(['teacher_id', 'school_class_id'], 'materials_teacher_class_index');
            }
        });

        // ── Tabel assignments ───────────────────────────────────────
        // `due_date` sering di-filter (whereDate due_date >= now)
        // `created_at` untuk ordering (latest())
        Schema::table('assignments', function (Blueprint $table) {
            if (!$this->hasIndex('assignments', 'assignments_due_date_index')) {
                $table->index('due_date', 'assignments_due_date_index');
            }
            if (!$this->hasIndex('assignments', 'assignments_created_at_index')) {
                $table->index('created_at', 'assignments_created_at_index');
            }
        });

        // ── Tabel forum_topics ──────────────────────────────────────
        // `is_pinned` untuk ordering (orderByDesc is_pinned)
        // `is_locked` sering dicek saat reply
        // `created_at` untuk latest()
        Schema::table('forum_topics', function (Blueprint $table) {
            if (!$this->hasIndex('forum_topics', 'forum_topics_is_pinned_index')) {
                $table->index('is_pinned', 'forum_topics_is_pinned_index');
            }
            if (!$this->hasIndex('forum_topics', 'forum_topics_is_locked_index')) {
                $table->index('is_locked', 'forum_topics_is_locked_index');
            }
            if (!$this->hasIndex('forum_topics', 'forum_topics_created_at_index')) {
                $table->index('created_at', 'forum_topics_created_at_index');
            }
        });

        // ── Tabel announcements ─────────────────────────────────────
        // `target_role` sering di-filter (whereIn target_role)
        // `created_at` untuk orderBy latest
        Schema::table('announcements', function (Blueprint $table) {
            if (!$this->hasIndex('announcements', 'announcements_target_role_index')) {
                $table->index('target_role', 'announcements_target_role_index');
            }
            if (!$this->hasIndex('announcements', 'announcements_created_at_index')) {
                $table->index('created_at', 'announcements_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_email_verified_at_index');
            $table->dropIndex('users_class_id_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_status_index');
            $table->dropIndex('attendances_date_index');
            $table->dropIndex('attendances_student_date_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('materials_created_at_index');
            $table->dropIndex('materials_teacher_class_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_due_date_index');
            $table->dropIndex('assignments_created_at_index');
        });

        Schema::table('forum_topics', function (Blueprint $table) {
            $table->dropIndex('forum_topics_is_pinned_index');
            $table->dropIndex('forum_topics_is_locked_index');
            $table->dropIndex('forum_topics_created_at_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_target_role_index');
            $table->dropIndex('announcements_created_at_index');
        });
    }

    /**
     * Helper: Periksa apakah index sudah ada (idempotent check).
     * Mendukung SQLite dan MySQL/MariaDB.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = collect(\DB::select("PRAGMA index_list(\"{$table}\")"));
            return $indexes->contains('name', $indexName);
        }

        // MySQL / MariaDB
        $indexes = collect(\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ));
        return $indexes->isNotEmpty();
    }
};
