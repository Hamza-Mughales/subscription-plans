<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlanSubscription $subscription,
        public int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return config('subscription-plans.notifications.channels', ['mail']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;

        return (new MailMessage)
            ->subject(__('subscription-plans::notifications.trial_ending.subject'))
            ->greeting(__('subscription-plans::notifications.trial_ending.greeting'))
            ->line(__('subscription-plans::notifications.trial_ending.line1', [
                'plan' => $plan->name,
                'days' => $this->daysRemaining,
            ]))
            ->line(__('subscription-plans::notifications.trial_ending.line2', [
                'trial_end' => $this->subscription->trial_ends_at->format('F d, Y'),
            ]))
            ->action(__('subscription-plans::notifications.subscribe_now'), url('/subscriptions/subscribe'))
            ->line(__('subscription-plans::notifications.thank_you'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_id'         => $this->subscription->plan_id,
            'plan_name'       => $this->subscription->plan->name,
            'trial_ends_at'   => $this->subscription->trial_ends_at->toISOString(),
            'days_remaining'  => $this->daysRemaining,
        ];
    }
}
