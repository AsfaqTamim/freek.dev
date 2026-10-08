<?php

use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\ConfirmSubscriberAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\CreateSubscriberAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\DeleteSubscriberAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\ImportSubscribersAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\SendConfirmSubscriberMailAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\SendWelcomeMailAction;
use Spatie\Mailcoach\Domain\Audience\Actions\Subscribers\UpdateSubscriberAction;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;
use Spatie\Mailcoach\Domain\Automation\Actions\SendAutomationMailsAction;
use Spatie\Mailcoach\Domain\Automation\Actions\SendAutomationMailTestAction;
use Spatie\Mailcoach\Domain\Automation\Actions\SendAutomationMailToSubscriberAction;
use Spatie\Mailcoach\Domain\Automation\Actions\ShouldAutomationRunForSubscriberAction;
use Spatie\Mailcoach\Domain\Automation\Models\Action;
use Spatie\Mailcoach\Domain\Automation\Models\ActionSubscriber;
use Spatie\Mailcoach\Domain\Automation\Models\Automation;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMailClick;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMailLink;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMailOpen;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMailUnsubscribe;
use Spatie\Mailcoach\Domain\Automation\Models\Trigger;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\AddTagsAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\ConditionAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\HaltAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\RemoveTagsAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\SendAutomationMailAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\SplitAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\UnsubscribeAction;
use Spatie\Mailcoach\Domain\Automation\Support\Actions\WaitAction;
use Spatie\Mailcoach\Domain\Automation\Support\Replacers\AutomationMailNameAutomationMailReplacer;
use Spatie\Mailcoach\Domain\Automation\Support\Replacers\WebviewAutomationMailReplacer;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\DateTrigger;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\NoTrigger;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\SubscribedTrigger;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\TagAddedTrigger;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\TagRemovedTrigger;
use Spatie\Mailcoach\Domain\Automation\Support\Triggers\WebhookTrigger;
use Spatie\Mailcoach\Domain\Campaign\Actions\ConvertHtmlToTextAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\PersonalizeHtmlAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\PersonalizeSubjectAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\PrepareEmailHtmlAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\PrepareSubjectAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\PrepareWebviewHtmlAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\RetrySendingFailedSendsAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\SendCampaignAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\SendCampaignTestAction;
use Spatie\Mailcoach\Domain\Campaign\Actions\SendMailAction;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignClick;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignLink;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignOpen;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignUnsubscribe;
use Spatie\Mailcoach\Domain\Campaign\Models\Template;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\CampaignNameCampaignReplacer;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\EmailListCampaignReplacer;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\SubscriberReplacer;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\UnsubscribeUrlReplacer;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\WebviewCampaignReplacer;
use Spatie\Mailcoach\Domain\Shared\Actions\CalculateStatisticsAction;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Support\Editor\TextEditor;
use Spatie\Mailcoach\Domain\TransactionalMail\Actions\RenderTemplateAction;
use Spatie\Mailcoach\Domain\TransactionalMail\Actions\SendTestForTransactionalMailTemplateAction;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMail;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMailTemplate;
use Spatie\Mailcoach\Domain\TransactionalMail\Support\Replacers\SubjectReplacer;
use Spatie\Mailcoach\Http\App\Middleware\Authenticate;
use Spatie\Mailcoach\Http\App\Middleware\Authorize;
use Spatie\Mailcoach\Http\App\Middleware\SetMailcoachDefaults;
use Spatie\MailcoachMonaco\MonacoEditor;

return [
    'campaigns' => [
        /*
         * The default mailer used by Mailcoach for sending campaigns.
         */
        'mailer' => null,

        /*
         * Replacers are classes that can make replacements in the html of a campaign.
         *
         * You can use a replacer to create placeholders.
         */
        'replacers' => [
            WebviewCampaignReplacer::class,
            SubscriberReplacer::class,
            EmailListCampaignReplacer::class,
            UnsubscribeUrlReplacer::class,
            CampaignNameCampaignReplacer::class,
        ],

        /*
         * Here you can configure which campaign template editor Mailcoach uses.
         * By default this is a text editor that highlights HTML.
         */
        'editor' => MonacoEditor::class,

        /*
         * Here you can specify which jobs should run on which queues.
         * Use an empty string to use the default queue.
         */
        'perform_on_queue' => [
            'send_campaign_job' => 'send-campaign',
            'send_mail_job' => 'send-mail',
            'send_test_mail_job' => 'mailcoach',
            'send_welcome_mail_job' => 'mailcoach',
            'process_feedback_job' => 'mailcoach-feedback',
            'import_subscribers_job' => 'mailcoach',
        ],

        /*
         * By default only 10 mails per second will be sent to avoid overwhelming your
         * e-mail sending service.
         */
        'throttling' => [
            'allowed_number_of_jobs_in_timespan' => 30,
            'timespan_in_seconds' => 1,

            /*
             * Throttling relies on the cache. Here you can specify the store to be used.
             *
             * When passing `null`, we'll use the default store.
             */
            'cache_store' => null,
        ],

        /*
         * The job that will send a campaign could take a long time when your list contains a lot of subscribers.
         * Here you can define the maximum run time of the job. If the job hasn't fully sent your campaign, it
         * will redispatch itself.
         */
        'send_campaign_maximum_job_runtime_in_seconds' => 60 * 10,

        /*
         * You can customize some of the behavior of this package by using our own custom action.
         * Your custom action should always extend the one of the default ones.
         */
        'actions' => [
            'prepare_email_html' => PrepareEmailHtmlAction::class,
            'prepare_subject' => PrepareSubjectAction::class,
            'prepare_webview_html' => PrepareWebviewHtmlAction::class,
            'convert_html_to_text' => ConvertHtmlToTextAction::class,
            'personalize_html' => PersonalizeHtmlAction::class,
            'personalize_subject' => PersonalizeSubjectAction::class,
            'retry_sending_failed_sends' => RetrySendingFailedSendsAction::class,
            'send_campaign' => SendCampaignAction::class,
            'send_mail' => SendMailAction::class,
            'send_test_mail' => SendCampaignTestAction::class,
        ],
    ],

    'automation' => [
        /*
         * The default mailer used by Mailcoach for automation mails.
         */
        'mailer' => null,

        /*
         * By default only 10 mails per second will be sent to avoid overwhelming your
         * e-mail sending service.
         */
        'throttling' => [
            'allowed_number_of_jobs_in_timespan' => 30,
            'timespan_in_seconds' => 1,

            /*
             * Throttling relies on the cache. Here you can specify the store to be used.
             *
             * When passing `null`, we'll use the default store.
             */
            'cache_store' => null,
        ],

        /*
         * The job that will send automation mails could take a long time when your list contains a lot of subscribers.
         * Here you can define the maximum run time of the job. If the job hasn't fully sent your automation mails, it
         * will redispatch itself.
         */
        'send_automation_mails_maximum_job_runtime_in_seconds' => 60 * 10,

        /*
         * Here you can configure which automation mail template editor Mailcoach uses.
         * By default this is a text editor that highlights HTML.
         */
        'editor' => MonacoEditor::class,

        'actions' => [
            'send_mail' => Spatie\Mailcoach\Domain\Automation\Actions\SendMailAction::class,
            'send_automation_mail_to_subscriber' => SendAutomationMailToSubscriberAction::class,
            'send_automation_mails_action' => SendAutomationMailsAction::class,
            'prepare_subject' => Spatie\Mailcoach\Domain\Automation\Actions\PrepareSubjectAction::class,
            'prepare_webview_html' => Spatie\Mailcoach\Domain\Automation\Actions\PrepareWebviewHtmlAction::class,

            'convert_html_to_text' => Spatie\Mailcoach\Domain\Automation\Actions\ConvertHtmlToTextAction::class,
            'prepare_email_html' => Spatie\Mailcoach\Domain\Automation\Actions\PrepareEmailHtmlAction::class,
            'personalize_html' => Spatie\Mailcoach\Domain\Automation\Actions\PersonalizeHtmlAction::class,
            'personalize_subject' => Spatie\Mailcoach\Domain\Automation\Actions\PersonalizeSubjectAction::class,
            'send_test_mail' => SendAutomationMailTestAction::class,

            'should_run_for_subscriber' => ShouldAutomationRunForSubscriberAction::class,
        ],

        'replacers' => [
            WebviewAutomationMailReplacer::class,
            Spatie\Mailcoach\Domain\Automation\Support\Replacers\SubscriberReplacer::class,
            Spatie\Mailcoach\Domain\Automation\Support\Replacers\UnsubscribeUrlReplacer::class,
            AutomationMailNameAutomationMailReplacer::class,
        ],

        'flows' => [
            /**
             * The available actions in the automation flows. You can add custom
             * actions to this array, make sure they extend
             * \Spatie\Mailcoach\Domain\Automation\Support\Actions\AutomationAction
             */
            'actions' => [
                AddTagsAction::class,
                SendAutomationMailAction::class,
                ConditionAction::class,
                SplitAction::class,
                RemoveTagsAction::class,
                WaitAction::class,
                HaltAction::class,
                UnsubscribeAction::class,
            ],

            /**
             * The available triggers in the automation settings. You can add
             * custom triggers to this array, make sure they extend
             * \Spatie\Mailcoach\Domain\Automation\Support\Triggers\AutomationTrigger
             */
            'triggers' => [
                NoTrigger::class,
                SubscribedTrigger::class,
                DateTrigger::class,
                TagAddedTrigger::class,
                TagRemovedTrigger::class,
                WebhookTrigger::class,
            ],

            /**
             * Custom conditions for the ConditionAction, these have to implement the
             * \Spatie\Mailcoach\Domain\Automation\Support\Conditions\Condition
             * interface.
             */
            'conditions' => [],
        ],

        'perform_on_queue' => [
            'dispatch_pending_automation_mails_job' => 'send-campaign',
            'run_automation_action_job' => 'send-campaign',
            'run_action_for_subscriber_job' => 'mailcoach',
            'run_automation_for_subscriber_job' => 'mailcoach',
            'send_automation_mail_to_subscriber_job' => 'send-automation-mail',
            'send_automation_mail_job' => 'send-mail',
            'send_test_mail_job' => 'mailcoach',
        ],
    ],

    'audience' => [
        'actions' => [
            'confirm_subscriber' => ConfirmSubscriberAction::class,
            'create_subscriber' => CreateSubscriberAction::class,
            'delete_subscriber' => DeleteSubscriberAction::class,
            'import_subscribers' => ImportSubscribersAction::class,
            'send_confirm_subscriber_mail' => SendConfirmSubscriberMailAction::class,
            'send_welcome_mail' => SendWelcomeMailAction::class,
            'update_subscriber' => UpdateSubscriberAction::class,
        ],

        /*
         * This disk will be used to store files regarding importing subscribers.
         */
        'import_subscribers_disk' => 'public',
    ],

    'transactional' => [
        /*
         * The default mailer used by Mailcoach for transactional mails.
         */
        'mailer' => null,

        /*
         * Replacers are classes that can make replacements in the body of transactional mails.
         *
         * You can use replacers to create placeholders.
         */
        'replacers' => [
            'subject' => SubjectReplacer::class,
        ],

        'actions' => [
            'send_test' => SendTestForTransactionalMailTemplateAction::class,
            'render_template' => RenderTemplateAction::class,
        ],

        /**
         * Here you can configure which transactional mail template editor Mailcoach uses.
         * By default this is a text editor that highlights HTML.
         */
        'editor' => TextEditor::class,
    ],

    'shared' => [
        /*
         * Here you can specify which jobs should run on which queues.
         * Use an empty string to use the default queue.
         */
        'perform_on_queue' => [
            'calculate_statistics_job' => 'mailcoach',
        ],

        'actions' => [
            'calculate_statistics' => CalculateStatisticsAction::class,
        ],
    ],

    /*
     * The mailer used by Mailcoach for password resets and summary emails.
     * Mailcoach will use the default Laravel mailer if this is not set.
     */
    'mailer' => null,

    /*
     * The date format used on all screens of the UI
     */
    'date_format' => 'Y-m-d H:i',

    /*
     * Here you can specify on which connection Mailcoach's jobs will be dispatched.
     * Leave empty to use the app default's env('QUEUE_CONNECTION')
     */
    'queue_connection' => '',

    /*
     * Unauthorized users will get redirected to this route.
     */
    'redirect_unauthorized_users_to_route' => 'login',

    /*
     *  This configuration option defines the authentication guard that will
     *  be used to protect your the Mailcoach UI. This option should match one
     *  of the authentication guards defined in the "auth" config file.
     */
    'guard' => env('MAILCOACH_GUARD', null),

    /*
     *  These middleware will be assigned to every Mailcoach routes, giving you the chance
     *  to add your own middleware to this stack or override any of the existing middleware.
     */
    'middleware' => [
        'web' => [
            'web',
            Authenticate::class,
            Authorize::class,
            SetMailcoachDefaults::class,
        ],
        'api' => [
            'api',
            'auth:api',
        ],
    ],

    'models' => [
        /*
         * The model you want to use as a Campaign model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\Campaign::class`
         * model.
         */
        'campaign' => Campaign::class,

        /*
         * The model you want to use as a CampaignLink model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\CampaignLink::class`
         * model.
         */
        'campaign_link' => CampaignLink::class,

        /*
         * The model you want to use as a CampaignClick model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\CampaignClick::class`
         * model.
         */
        'campaign_click' => CampaignClick::class,

        /*
         * The model you want to use as a CampaignOpen model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\CampaignOpen::class`
         * model.
         */
        'campaign_open' => CampaignOpen::class,

        /*
         * The model you want to use as a CampaignUnsubscribe model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\CampaignUnsubscribe::class`
         * model.
         */
        'campaign_unsubscribe' => CampaignUnsubscribe::class,

        /*
         * The model you want to use as a EmailList model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Audience\Models\EmailList::class`
         * model.
         */
        'email_list' => EmailList::class,

        /*
         * The model you want to use as a Send model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Shared\Models\Send::class`
         * model.
         */
        'send' => Send::class,

        /*
         * The model you want to use as a Subscriber model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Audience\Models\Subscriber::class`
         * model.
         */
        'subscriber' => Subscriber::class,

        /*
         * The model you want to use as a Template model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Campaign\Models\Template::class`
         * model.
         */
        'template' => Template::class,

        /*
         * The model you want to use as a TransactionalMail model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMail::class`
         * model.
         */
        'transactional_mail' => TransactionalMail::class,

        /*
         * The model you want to use as a TransactionalMailTemplate model. It needs to be or
         * extend the `\Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMailTemplate::class`
         * model.
         */
        'transactional_mail_template' => TransactionalMailTemplate::class,

        /*
         * The model you want to use as an Automation model. It needs to be or
         * extend the `\Spatie\Mailcoach\Domain\Automation\Models\Automation::class`
         * model.
         */
        'automation' => Automation::class,

        /*
         * The model you want to use as an Action model. It needs to be or
         * extend the `\Spatie\Mailcoach\Domain\Automation\Models\Action::class`
         * model.
         */
        'automation_action' => Action::class,

        /*
         * The model you want to use as a Trigger model. It needs to be or
         * extend the `\Spatie\Mailcoach\Domain\Automation\Models\Trigger::class`
         * model.
         */
        'automation_trigger' => Trigger::class,

        /*
         * The model you want to use as an Automation mail model. It needs to be or
         * extend the `\Spatie\Mailcoach\Domain\Automation\Models\AutomationMail::class` model.
         */
        'automation_mail' => AutomationMail::class,

        /*
         * The model you want to use as a Campaign model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Automation\Models\AutomationMailLink::class`
         * model.
         */
        'automation_mail_link' => AutomationMailLink::class,

        /*
         * The model you want to use as a Campaign model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Automation\Models\AutomationMailClick::class`
         * model.
         */
        'automation_mail_click' => AutomationMailClick::class,

        /*
         * The model you want to use as a Campaign model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Automation\Models\AutomationMailOpen::class`
         * model.
         */
        'automation_mail_open' => AutomationMailOpen::class,

        /*
         * The model you want to use as a Campaign model. It needs to be or
         * extend the `Spatie\Mailcoach\Domain\Automation\Models\AutomationMailUnsubscribe::class`
         * model.
         */
        'automation_mail_unsubscribe' => AutomationMailUnsubscribe::class,

        /*
         * The model you want to use as the pivot between an Automation Action model
         * and the Subscriber model. It needs to be or extend the
         * `\Spatie\Mailcoach\Domain\Automation\Models\ActionSubscriber::class` model.
         */
        'action_subscriber' => ActionSubscriber::class,
    ],

    'views' => [
        /*
         * The service provider registers several Blade components that are
         * used in Mailcoach's views. If you are using the default Mailcoach
         * views, leave this as true so they work as expected. If you have
         * your own views and don't need/want Mailcoach to register these
         * blade components (e.g., because of naming conflicts), you can
         * change this setting to false and they won't be registered.
         *
         * If you change this setting, be sure to run `php artisan view:clear`
         * so Laravel can recompile your views.
         */
        'use_blade_components' => true,
    ],

    'mailgun_feedback' => [
        'signing_secret' => env('MAILGUN_SIGNING_SECRET'),
    ],
];
