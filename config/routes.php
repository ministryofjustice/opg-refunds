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

//  Unauthenticated routes
$app->route('/sign-in', App\Action\SignInAction::class, ['GET', 'POST'], 'sign.in');
$app->get('/sign-out', App\Action\SignOutAction::class, 'sign.out');
$app->get('/reset-password', App\Action\PasswordRequestResetAction::class, 'password.request.reset');

//  Authenticated routes - see AuthorizationMiddleware
$app->get('/', App\Action\HomePageAction::class, 'home');
$app->get('/admin', App\Action\AdminAction::class, 'admin');
$app->get('/caseworker', App\Action\CaseworkerAction::class, 'caseworker');
$app->get('/refund', App\Action\RefundAction::class, 'refund');
$app->get('/reporting', App\Action\ReportingAction::class, 'reporting');
$app->get('/set-password', App\Action\PasswordSetNewAction::class, 'password.set.new');
$app->get('/download', App\Action\DownloadAction::class, 'download');
$app->get('/csv-download', App\Action\CsvDownloadAction::class, 'csv.download');
$app->get('/process-new-claim', App\Action\ProcessNewClaimAction::class, 'process.new.claim');
$app->route('/claim/{id:\d+}', App\Action\ClaimAction::class, ['GET', 'POST'], 'claim');
$app->route('/claim/{claimId:\d+}/poa/sirius[/{id:\d+}]', App\Action\Poa\PoaSiriusAction::class, ['GET', 'POST'], 'claim.poa.sirius');
$app->get('/claim/{id:\d+}/poa/sirius/none-found', App\Action\Poa\PoaSiriusNoneFoundAction::class, 'claim.poa.sirius.none.found');
$app->get('/claim/{id:\d+}/poa/sirius/cancel-none-found', App\Action\Poa\PoaSiriusCancelNoneFoundAction::class, 'claim.poa.sirius.cancel.none.found');
$app->route('/claim/{claimId:\d+}/poa/meris[/{id:\d+}]', App\Action\Poa\PoaMerisAction::class, ['GET', 'POST'], 'claim.poa.meris');
$app->get('/claim/{id:\d+}/poa/meris/none-found', App\Action\Poa\PoaMerisNoneFoundAction::class, 'claim.poa.meris.none.found');
$app->get('/claim/{id:\d+}/poa/meris/cancel-none-found', App\Action\Poa\PoaMerisCancelNoneFoundAction::class, 'claim.poa.meris.cancel.none.found');
