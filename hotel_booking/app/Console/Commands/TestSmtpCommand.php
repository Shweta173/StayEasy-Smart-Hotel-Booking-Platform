<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-smtp {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMTP configuration by sending a simple test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Enter the email address to send test email to:');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Testing SMTP configuration...');
        $this->info('');
        
        // Show current mail configuration
        $this->info('Current Mail Settings:');
        $this->info('MAIL_MAILER: ' . config('mail.mailer'));
        $this->info('MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->info('MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->info('MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->info('MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->info('');

        if (config('mail.mailer') === 'log') {
            $this->warn('⚠️  MAIL_MAILER is set to "log"');
            $this->warn('Emails will be written to storage/logs/laravel.log instead of being sent.');
            $this->info('');
            $this->info('To send real emails, update your .env file:');
            $this->info('MAIL_MAILER=smtp');
            $this->info('');
            
            $continue = $this->confirm('Continue with test anyway?', true);
            if (!$continue) {
                return 0;
            }
            $this->info('');
        }

        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from Hotel Booking Platform. If you received this, your SMTP configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('SMTP Test - Hotel Booking Platform');
            });

            $this->info('');
            $this->info('✅ Email sent successfully!');
            $this->info('');

            if (config('mail.mailer') === 'log') {
                $this->info('Check: storage/logs/laravel.log');
            } else {
                $this->info("Check the inbox for: {$email}");
                $this->info('(Check spam folder if not in inbox)');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('');
            $this->error('❌ Failed to send email!');
            $this->error('');
            $this->error('Error: ' . $e->getMessage());
            $this->error('');
            $this->info('Troubleshooting:');
            $this->info('1. Check your .env file has correct SMTP settings');
            $this->info('2. Verify MAIL_USERNAME and MAIL_PASSWORD are correct');
            $this->info('3. For Gmail, use an App Password (not regular password)');
            $this->info('4. Run: php artisan config:clear');
            $this->info('5. Check firewall/antivirus settings');

            return 1;
        }
    }
}
