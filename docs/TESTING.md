# Running the regression suite

Use PHP 8.4 or newer for the currently locked development dependencies, Composer,
and an **isolated PostgreSQL test database**. PHP needs PDO PostgreSQL, mbstring,
intl, XML/DOM, and GD (contact-form CAPTCHA); use a writable PHP session directory.
The legacy model tests use PostgreSQL `TRUNCATE ... CASCADE` and must never point
at a development or production database containing real data.

1. Run `composer install` and `php init --env=Development --overwrite=n`.
2. In the ignored `common/config/test-local.php`, merge the normal common
   configuration as the generated template does, but override `db` with the
   disposable PostgreSQL database DSN, username and password. Retain the mailer
   `viewPath` (`@common/mail`) and `useFileTransport => true` from the development
   configuration. No mail should be sent externally by tests.
3. Apply migrations to **that test database only**:
   `php yii_test migrate --interactive=0`.
4. Run:

   ```sh
   composer validate --strict
   composer audit --locked
   php vendor/bin/codecept build
   php vendor/bin/codecept run
   ```

Individual suites: `php vendor/bin/codecept run unit -c common`,
`php vendor/bin/codecept run unit -c frontend`, and
`php vendor/bin/codecept run unit -c backend`. Functional suites use the Yii2
in-process browser and do not need an externally served website. Acceptance
browser tests are not configured by default.

## Accounting regression

`Fund.current_balance` includes invested principal. Available cash is that total
minus active investments. An investment of 5,000 from a 10,000 fund leaves total
assets at 10,000 and available cash at 5,000; withdrawing it restores available
cash to 10,000 without increasing total assets. Tests cover repeated withdrawals,
repeated investment/withdrawal cycles, and withdrawing one of two investments.
The application exposes whole-investment withdrawal, not partial redemption of a
single investment.

This correction prevents future double credits. It does **not** reconcile balances
previously inflated by withdrawals. Any historical correction requires reviewing
real transaction/ledger evidence separately; do not mechanically subtract old
withdrawals or run test cleanup against real data.
