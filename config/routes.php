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

$app->get('/', App\Action\HomePageAction::class, 'home');
$app->get('/test', App\Action\TestAction::class, 'test');
$app->get('/api/ping', App\Action\PingAction::class, 'api.ping');


$app->route('/when-were-fees-paid', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when');
$app->route('/when-were-fees-paid/answer', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when.answer');

$app->route('/who-is-applying', App\Action\WhoAction::class, ['GET'], 'eligibility.who');
$app->route('/who-is-applying/answer', App\Action\WhoAction::class, ['GET'], 'eligibility.who.answer');

$app->route('/donor-status', App\Action\DonorDeceasedAction::class, ['GET'], 'eligibility.deceased');
$app->route('/donor-status/answer', App\Action\DonorDeceasedAction::class, ['GET'], 'eligibility.deceased.answer');


$app->route('/{who:donor|attorney}-applying/donor-details', App\Action\DonorDetailsAction::class, ['GET', 'POST'], 'apply.donor');
$app->route('/{who:donor|attorney}-applying/attorney-details', App\Action\AttorneyDetailsAction::class, ['GET', 'POST'], 'apply.attorney');
$app->route('/{who:donor|attorney}-applying/verification', App\Action\VerificationDetailsAction::class, ['GET', 'POST'], 'apply.verification');

$app->route('/{who:donor|attorney}-applying/contact', App\Action\ContactDetailsAction::class, ['GET', 'POST'], 'apply.contact');
$app->route('/{who:donor|attorney}-applying/summary', App\Action\SummaryAction::class, ['GET'], 'apply.summary');
$app->route('/{who:donor|attorney}-applying/account-details', App\Action\AccountDetailsAction::class, ['GET', 'POST'], 'apply.account');
$app->route('/{who:donor|attorney}-applying/done', App\Action\DoneAction::class, ['GET'], 'apply.done');
