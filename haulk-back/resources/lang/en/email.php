<?php

return [
    'saas' => [
        'company_registration' => [
            'confirm_company' => [
                'subject' => 'New carrier application for your review.',
                'body' => "A new application is approved for :company_name",
                'button' => "Review",
            ]
        ],
        'company' => [
            'billing' => [
                'trial_end' => [
                    'subject' => "Free Trial Was a Success",
                    'greeting' => "Hi :name,",
                    'body' => "Your free trial has been a journey of discovery! We hope you've enjoyed experiencing Haulk's features. Keep up the good work and make full use of all the features."
                ],
                'before_trial_end' => [
                    'subject' => "Last Day of Your Free Trial - Don’t Miss Out!",
                    'greeting' => "Hi :name,",
                    'body' => "Today is the last day of your free trial. Tomorrow your profile will be upgraded to a paid plan and you will continue your journey with Haulk with access to our powerful tools."
                ],
                'after_not_paid_first_remind' => [
                    'subject' => "Problem with your subscription in Haulk CRM",
                    'greeting' => "Hi :name,",
                    'body' => "We are waiting for you back in Haulk CRM. We'd love for you to continue using our full range of features. Consider solving the issues with your payment method to use a paid plan today."
                ],
                'after_not_paid_second_remind' => [
                    'subject' => "Miss Your Haulk Experience?",
                    'greeting' => "Hi :name,",
                    'body' => "We noticed that you have not taken any action after issues with your payment method. Connect to Haulk and continue to manage your transportation needs effortlessly by solving the issues with your payment data and upgrading to a paid plan."
                ]
            ],
            'payment_card' => [
                'add' => [
                    'subject' => "Payment Method Added Successfully",
                    'greeting' => "Hi :name,",
                    'body' => "Your credit/debit card has been successfully added to your Haulk account. You're now all set for smooth and secure transactions. Explore our services with ease!",
                ],
                'problem_with_add' => [
                    'subject' => "There's a Hiccup Adding Your Card to Haulk",
                    'greeting' => "Hello :name,",
                    'body' => "We noticed there was an issue adding your credit/debit card. Let's sort this out together so you can enjoy uninterrupted services on Haulk.",
                ],
                'not_add_first_remind' => [
                    'subject' => "Reminder: Add Your Payment Method to Continue with Haulk",
                    'greeting' => "Hi :name,",
                    'body' => "Just a quick reminder to add your credit/debit card to your Haulk account. It's a simple step to ensure continuous access to our features.",
                ],
                'not_add_second_remind' => [
                    'subject' => "Your Haulk Account Awaits Your Payment Details",
                    'greeting' => "Hi :name,",
                    'body' => "Adding your credit/debit card is critical to your Haulk experience. Ensure seamless access to our services by adding your payment method today.",
                ],
                'not_add_final_remind' => [
                    'subject' => "Last Reminder: Add Your Card to Your Haulk Account",
                    'greeting' => "Hi :name,",
                    'body' => "This is your final reminder to add your credit/debit card to your Haulk account. Don’t miss out on the convenience and efficiency Haulk offers.",
                ],
            ],
            'login' => [
                'with_free_trial' => [
                    'subject' => "Welcome to Your Haulk Free Trial!",
                    'greeting' => "Hi :name,",
                    'body' => "Congratulations on starting your free trial with Haulk! You're now on the road to experiencing how we can streamline your transportation management. Dive in and explore all the features available to you.",
                ],
                'not_free_trial_first_remind' => [
                    'subject' => "Make the Most of Your Haulk Free Trial",
                    'greeting' => "Hello :name,",
                    'body' => "We noticed you haven't started using your Haulk free trial yet. Jump in to discover the tools and features we've designed for you.",
                ],
                'not_free_trial_second_remind' => [
                    'subject' => "Your Free Trial is Waiting - Explore Haulk Now",
                    'greeting' => "Hi :name,",
                    'body' => "Maximize your Haulk free trial experience. Start using our platform today to see how we can streamline your transportation management.",
                ],
                'not_free_trial_final_remind' => [
                    'subject' => "Final Call to Explore Your Haulk Free Trial",
                    'greeting' => "Hi :name,",
                    'body' => "Your free trial is halfway through, and we noticed that you haven't taken full advantage of it yet.Don’t miss out on experiencing all that Haulk has to offer!",
                ]
            ]
        ]
    ],
];
