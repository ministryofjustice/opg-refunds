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
$app->post('/v1/auth', Auth\Action\AuthAction::class, 'auth');
$app->route('/v1/user-by-token/{token}', App\Action\UserAction::class, ['GET', 'PATCH'], 'user.by.token');
$app->patch('/v1/reset-password', App\Action\PasswordResetAction::class, 'password.reset');
$app->get('/ping', App\Action\PingAction::class, 'ping');

//  Authenticated routes
$app->route('/v1/claim/{id:\d+}', App\Action\ClaimAction::class, ['GET', 'PATCH'], 'claim');
$app->route('/v1/claim/{claimId:\d+}/note[/{id:\d+}]', App\Action\ClaimNoteAction::class, ['GET', 'POST'], 'claim.log');
$app->route('/v1/claim/{claimId:\d+}/poa[/{id:\d+}]', App\Action\ClaimPoaAction::class, ['GET', 'POST', 'PUT', 'DELETE'], 'claim.poa');
$app->get('/v1/claim/search', App\Action\ClaimSearchAction::class, 'claim.search');
$app->route('/v1/user[/{id:\d+}]', App\Action\UserAction::class, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], 'user');
$app->route('/v1/user/{id:\d+}/claim[/{claimId:\d+}]', App\Action\UserClaimAction::class, ['GET', 'PUT', 'DELETE'], 'user.claim');
$app->get('/v1/user/search', App\Action\UserSearchAction::class, 'user.search');
$app->route('/v1/spreadsheet[/{date:\d{4}-\d{2}-\d{2}}]', App\Action\SpreadsheetAction::class, ['GET', 'POST'], 'spreadsheet');
$app->post('/v1/notify', App\Action\NotifyAction::class, 'notify');
$app->get('/v1/report', App\Action\ReportingAction::class, 'report');
