# Bundled Notifications for Laravel

Spamming your users with notifications? Want to reduce the noise your app is creating? You are in the right place!

This package can bundle transactional notifications you send from your application into a single notification, be it email, text messages, push notifications, or whatever else. Not only does it bundle notifications, you can also implement a do not distrub / work can wait time period in which all notifications will be delayed. After the period is over, all notifications sent during that time will be bundled into a single notification.

This package is inspired by the functionality offered by basecamp.

## How bundling works




## My Notes
- No config. Container binding is the config.
- Basic throttle per notifiable
- Work can wait / Do not disturb

## Coming later
- Throttle per notification type
- Ordering by notification type

## Unknown
- Should i release the reserved notifications when the job fails?
