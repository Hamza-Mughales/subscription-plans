<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlanSubscription $subscription
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
            ->subject(__('subscription-plans::notifications.subscription_expired.subject'))
            ->greeting(__('subscription-plans::notifications.subscription_expired.greeting'))
            ->line(__('subscription-plans::notifications.subscription_expired.line1', ['plan' => $plan->name]))
            ->line(__('subscription-plans::notifications.subscription_expired.line2'))
            ->action(__('subscription-plans::notifications.view_plans'), url('/plans'))
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
            'ended_at'        => $this->subscription->ends_at->toISOString(),
        ];
    }
}
