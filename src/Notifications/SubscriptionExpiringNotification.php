<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
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
            ->subject(__('subscription-plans::notifications.subscription_expiring.subject'))
            ->greeting(__('subscription-plans::notifications.subscription_expiring.greeting'))
            ->line(__('subscription-plans::notifications.subscription_expiring.line1', [
                'plan' => $plan->name,
                'days' => $this->daysRemaining,
            ]))
            ->line(__('subscription-plans::notifications.subscription_expiring.line2', [
                'end_date' => $this->subscription->ends_at->format('F d, Y'),
            ]))
            ->action(__('subscription-plans::notifications.renew_subscription'), url('/subscriptions/renew'))
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
            'ends_at'         => $this->subscription->ends_at->toISOString(),
            'days_remaining'  => $this->daysRemaining,
        ];
    }
}
