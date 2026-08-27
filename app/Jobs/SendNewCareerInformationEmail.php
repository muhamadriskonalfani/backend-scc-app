<?php

namespace App\Jobs;

use App\Mail\NewCareerInformationMail;
use App\Models\CareerInformation;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewCareerInformationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public CareerInformation $career
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new NewCareerInformationMail(
                $this->user,
                $this->career
            )
        );
        
        /* 
            Mailtrap punya batas pengiriman yaitu 1 email per 10 detik (untuk versi gratis),
            Gunakan "sleep(10)" untuk memberi jeda 10 detik,
            Jika sudah pindah ke Brevo anda bisa komentar kode di bawah ini 
        */
        // sleep(10); 
    }
}
