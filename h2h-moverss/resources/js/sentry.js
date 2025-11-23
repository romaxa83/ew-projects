// https://docs.sentry.io/platforms/javascript/guides/vue/configuration/integrations/plugin/
import * as Sentry from "@sentry/browser";
import { CaptureConsole as CaptureConsoleIntegration, ReportingObserver as ReportingObserverIntegration } from "@sentry/integrations";

Sentry.init({
    dsn: 'https://ec2e493a749a46e69c6dcb5a0d1063c7@glitchtip.thebigidea.com.ua/1',
    integrations: [
        new ReportingObserverIntegration(),
        new CaptureConsoleIntegration(
            {
                // array of methods that should be captured
                // defaults to ['log', 'info', 'warn', 'error', 'debug', 'assert']
                levels: ['warn', 'error']
            }
        )
    ],
    // integrations: [new Integrations.Vue({ Vue, attachProps: true })],
});
