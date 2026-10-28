<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Send a test email to verify mail configuration';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Sending test email to: ' . $email);

        try {
            Mail::raw('This is a test email from Hotel Booking Platform. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Hotel Booking Platform');
            });

            $this->info('✓ Email sent successfully!');
            $this->info('Check your inbox (and spam folder) for the test email.');
            
        } catch (\Exception $e) {
            $this->error('✗ Failed to send email: ' . $e->getMessage());
            $this->error('Full error: ' . $e->getTraceAsString());
        }
    }
}
