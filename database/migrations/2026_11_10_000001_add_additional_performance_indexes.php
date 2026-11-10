<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Tambahan Index Database untuk Optimasi Query Aksi & Navigasi Cepat
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel users ────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_subject_id_index')) {
                $table->index('subject_id', 'users_subject_id_index');
            }
        });

        // ── Tabel schedules ────────────────────────────────────────
        Schema::table('schedules', function (Blueprint $table) {
            if (!$this->hasIndex('schedules', 'schedules_day_index')) {
                $table->index('day', 'schedules_day_index');
            }
            if (!$this->hasIndex('schedules', 'schedules_start_time_index')) {
                $table->index('start_time', 'schedules_start_time_index');
            }
            if (!$this->hasIndex('schedules', 'schedules_class_day_index')) {
                $table->index(['school_class_id', 'day'], 'schedules_class_day_index');
            }
            if (!$this->hasIndex('schedules', 'schedules_teacher_day_index')) {
                $table->index(['teacher_id', 'day'], 'schedules_teacher_day_index');
            }
        });

        // ── Tabel submissions ──────────────────────────────────────
        Schema::table('submissions', function (Blueprint $table) {
            if (!$this->hasIndex('submissions', 'submissions_student_id_index')) {
                $table->index('student_id', 'submissions_student_id_index');
            }
            if (!$this->hasIndex('submissions', 'submissions_assignment_id_index')) {
                $table->index('assignment_id', 'submissions_assignment_id_index');
            }
            if (!$this->hasIndex('submissions', 'submissions_grade_index')) {
                $table->index('grade', 'submissions_grade_index');
            }
            if (!$this->hasIndex('submissions', 'submissions_submitted_at_index')) {
                $table->index('submitted_at', 'submissions_submitted_at_index');
            }
            if (!$this->hasIndex('submissions', 'submissions_student_grade_index')) {
                $table->index(['student_id', 'grade'], 'submissions_student_grade_index');
            }
            if (!$this->hasIndex('submissions', 'submissions_assignment_student_index')) {
                $table->index(['assignment_id', 'student_id'], 'submissions_assignment_student_index');
            }
        });

        // ── Tabel announcement_user ────────────────────────────────
        Schema::table('announcement_user', function (Blueprint $table) {
            if (!$this->hasIndex('announcement_user', 'announcement_user_read_at_index')) {
                $table->index('read_at', 'announcement_user_read_at_index');
            }
            if (!$this->hasIndex('announcement_user', 'announcement_user_user_read_index')) {
                $table->index(['user_id', 'read_at'], 'announcement_user_user_read_index');
            }
            if (!$this->hasIndex('announcement_user', 'announcement_user_ann_user_index')) {
                $table->index(['announcement_id', 'user_id'], 'announcement_user_ann_user_index');
            }
        });

        // ── Tabel reported_replies ─────────────────────────────────
        Schema::table('reported_replies', function (Blueprint $table) {
            if (!$this->hasIndex('reported_replies', 'reported_replies_status_index')) {
                $table->index('status', 'reported_replies_status_index');
            }
        });

        // ── Tabel forum_replies ────────────────────────────────────
        Schema::table('forum_replies', function (Blueprint $table) {
            if (!$this->hasIndex('forum_replies', 'forum_replies_created_at_index')) {
                $table->index('created_at', 'forum_replies_created_at_index');
            }
            if (!$this->hasIndex('forum_replies', 'forum_replies_topic_created_index')) {
                $table->index(['forum_topic_id', 'created_at'], 'forum_replies_topic_created_index');
            }
        });

        // ── Tabel school_classes ───────────────────────────────────
        Schema::table('school_classes', function (Blueprint $table) {
            if (!$this->hasIndex('school_classes', 'school_classes_grade_level_index')) {
                $table->index('grade_level', 'school_classes_grade_level_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_subject_id_index');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_day_index');
            $table->dropIndex('schedules_start_time_index');
            $table->dropIndex('schedules_class_day_index');
            $table->dropIndex('schedules_teacher_day_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_student_id_index');
            $table->dropIndex('submissions_assignment_id_index');
            $table->dropIndex('submissions_grade_index');
            $table->dropIndex('submissions_submitted_at_index');
            $table->dropIndex('submissions_student_grade_index');
            $table->dropIndex('submissions_assignment_student_index');
        });

        Schema::table('announcement_user', function (Blueprint $table) {
            $table->dropIndex('announcement_user_read_at_index');
            $table->dropIndex('announcement_user_user_read_index');
            $table->dropIndex('announcement_user_ann_user_index');
        });

        Schema::table('reported_replies', function (Blueprint $table) {
            $table->dropIndex('reported_replies_status_index');
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->dropIndex('forum_replies_created_at_index');
            $table->dropIndex('forum_replies_topic_created_index');
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropIndex('school_classes_grade_level_index');
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

