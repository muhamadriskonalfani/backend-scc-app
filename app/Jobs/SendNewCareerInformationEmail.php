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
        
        sleep(10); // Jeda mailtrap versi gratisan, 1 email per 10 detik  
    }
}
