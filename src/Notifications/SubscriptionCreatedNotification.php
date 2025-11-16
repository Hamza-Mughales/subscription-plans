<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

class SubscriptionCreatedNotification extends Notification implements ShouldQueue
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
            ->subject(__('subscription-plans::notifications.subscription_created.subject'))
            ->greeting(__('subscription-plans::notifications.subscription_created.greeting'))
            ->line(__('subscription-plans::notifications.subscription_created.line1', ['plan' => $plan->name]))
            ->line(__('subscription-plans::notifications.subscription_created.line2', [
                'start_date' => $this->subscription->starts_at->format('F d, Y'),
                'end_date'   => $this->subscription->ends_at->format('F d, Y'),
            ]))
            ->when($this->subscription->onTrial(), function ($mail) {
                return $mail->line(__('subscription-plans::notifications.subscription_created.trial', [
                    'trial_end' => $this->subscription->trial_ends_at->format('F d, Y'),
                ]));
            })
            ->action(__('subscription-plans::notifications.view_subscription'), url('/subscriptions'))
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
            'starts_at'       => $this->subscription->starts_at->toISOString(),
            'ends_at'         => $this->subscription->ends_at->toISOString(),
        ];
    }
}
