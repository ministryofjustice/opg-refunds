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
$app->get('/', App\Action\HomeAction::class, 'home');
$app->get('/admin', App\Action\AdminAction::class, 'admin');
$app->get('/caseworker[/{id:\d+}]', App\Action\CaseworkerAction::class, 'caseworker');
$app->route('/caseworker/{action:add}', App\Action\CaseworkerAction::class, ['GET', 'POST'], 'caseworker.add');
$app->route('/caseworker/{action:edit}/{id:\d+}', App\Action\CaseworkerAction::class, ['GET', 'POST'], 'caseworker.edit');
$app->post('/caseworker/{action:delete}/{id:\d+}', App\Action\CaseworkerAction::class, 'caseworker.delete');
$app->get('/refund', App\Action\HomeRefundAction::class, 'refund.home');
$app->get('/reporting', App\Action\HomeReportingAction::class, 'reporting.home');
$app->get('/set-password', App\Action\PasswordSetNewAction::class, 'password.set.new');
$app->get('/download', App\Action\DownloadAction::class, 'download');
$app->get('/csv-download', App\Action\CsvDownloadAction::class, 'csv.download');
$app->route('/claim[/{id:\d+}]', App\Action\ClaimAction::class, ['GET', 'POST'], 'claim');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}[/{id:\d+}]', App\Action\Poa\PoaAction::class, ['GET', 'POST'], 'claim.poa');
$app->post('/claim/{id:\d+}/poa/{system:sirius|meris}/none-found', App\Action\Poa\PoaNoneFoundAction::class, 'claim.poa.none.found');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}/{id:\d+}/delete', App\Action\Poa\PoaDeleteAction::class, ['GET', 'POST'], 'claim.poa.delete');
$app->route('/claim/{claimId:\d+}/approve', App\Action\Claim\ClaimAcceptAction::class, ['GET', 'POST'], 'claim.approve');
$app->route('/claim/{claimId:\d+}/reject', App\Action\Claim\ClaimRejectAction::class, ['GET', 'POST'], 'claim.reject');
