<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $latestJobs = collect();
        $latestPosts = collect();
        $stats = [
            'users'     => 0,
            'jobs'      => 0,
            'companies' => 0,
            'courses'   => 0,
        ];

        // Tenta carregar dados reais quando os models existirem
        try {
            if (class_exists(\App\Models\JobListing::class)) {
                $latestJobs = \App\Models\JobListing::query()
                    ->latest()
                    ->take(5)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Silencia — DB pode ainda não estar populada
        }

        try {
            if (class_exists(\App\Models\Post::class)) {
                $latestPosts = \App\Models\Post::query()
                    ->latest()
                    ->take(3)
                    ->get();
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            $stats['users'] = \App\Models\User::query()->count();
        } catch (\Throwable $e) {
            //
        }

        try {
            if (class_exists(\App\Models\JobListing::class)) {
                $stats['jobs'] = \App\Models\JobListing::query()->count();
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            if (class_exists(\App\Models\CompanyProfile::class)) {
                $stats['companies'] = \App\Models\CompanyProfile::query()->count();
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            if (class_exists(\App\Models\Course::class)) {
                $stats['courses'] = \App\Models\Course::query()->count();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('landing', [
            'latestJobs'  => $latestJobs,
            'latestPosts' => $latestPosts,
            'stats'       => $stats,
        ]);
    }
}
