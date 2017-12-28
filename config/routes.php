<?php
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

//Assisted digital entry point
$app->get('/assisted-digital/{token}', App\Action\AssistedDigitalAction::class, 'ad');

$app->get('/beta/{betaId:\d+}/{betaExpires:\d+}/{betaSignature:[A-Za-z0-9]+}', App\Action\BetaAction::class, 'beta');

//---

$app->get('/', App\Action\HomePageAction::class, 'home');
$app->get('/exception', App\Action\ExceptionAction::class, 'exception');
$app->get('/healthcheck.json', App\Action\HealthCheckAction::class, 'healthcheck.json');

$app->get('/terms', App\Action\TermsPageAction::class, 'terms');
$app->get('/cookies', App\Action\CookiesPageAction::class, 'cookies');
$app->get('/contact-us', App\Action\ContactUsAction::class, 'contact');

$app->get('/session-finished', App\Action\SessionFinishedAction::class, 'session');

//---

$app->route('/when-were-fees-paid', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when');
$app->route('/when-were-fees-paid/answer', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when.answer');

$app->get('/cookies-check', App\Action\CookiesCheckAction::class, 'cookies.check');

//---

$prefix = '/application';

$app->route($prefix.'/who-is-applying', App\Action\WhoAction::class, ['GET', 'POST'], 'apply.who');
$app->route($prefix.'/donor-status', App\Action\DonorDeceasedAction::class, ['GET', 'POST'], 'apply.deceased');
$app->route($prefix.'/donor-details', App\Action\DonorDetailsAction::class, ['GET', 'POST'], 'apply.donor');
$app->route($prefix.'/attorney-details', App\Action\AttorneyDetailsAction::class, ['GET', 'POST'], 'apply.attorney');
$app->route($prefix.'/case-number', App\Action\CaseNumberAction::class, ['GET', 'POST'], 'apply.case');
$app->route($prefix.'/postcode', App\Action\PostcodeAction::class, ['GET', 'POST'], 'apply.postcode');
$app->route($prefix.'/contact', App\Action\ContactDetailsAction::class, ['GET', 'POST'], 'apply.contact');
$app->route($prefix.'/account-details', App\Action\AccountDetailsAction::class, ['GET', 'POST'], 'apply.account');
$app->route($prefix.'/summary', App\Action\SummaryAction::class, ['GET', 'POST'], 'apply.summary');
$app->route($prefix.'/done', App\Action\DoneAction::class, ['GET'], 'apply.done');
