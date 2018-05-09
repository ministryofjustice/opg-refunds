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
$app->route('/reset-password', App\Action\Password\PasswordResetAction::class, ['GET', 'POST'], 'password.reset');
$app->get('/exception', App\Action\ExceptionAction::class, 'exception');

//  Authenticated routes - see AuthorizationMiddleware
$app->get('/', App\Action\Home\HomeAction::class, 'home');
$app->route('/user[/{id:\d+}]', App\Action\User\UserAction::class, ['GET', 'POST'], 'user');
$app->route('/user/add', App\Action\User\UserUpdateAction::class, ['GET', 'POST'], 'user.add');
$app->route('/user/edit/{id:\d+}', App\Action\User\UserUpdateAction::class, ['GET', 'POST'], 'user.edit');
$app->route('/user/delete/{id:\d+}', App\Action\User\UserDeleteAction::class, ['GET', 'POST'], 'user.delete');
$app->get('/refund', App\Action\RefundAction::class, 'refund');
$app->get('/reporting', App\Action\ReportingAction::class, 'reporting');
$app->route('/change-password[/{token}]', App\Action\Password\PasswordChangeAction::class, ['GET', 'POST'], 'password.change');
$app->get('/download[/{date:\d{4}-\d{2}-\d{2}}]', App\Action\DownloadAction::class, 'download');
$app->route('/verify', App\Action\VerifyAction::class, ['GET', 'POST'], 'verify');
$app->route('/claim[/{id:\d+}]', App\Action\Claim\ClaimAction::class, ['GET', 'POST'], 'claim');
$app->route('/claim/{claimId:\d+}/approve', App\Action\Claim\ClaimApproveAction::class, ['GET', 'POST'], 'claim.approve');
$app->route('/claim/{claimId:\d+}/reject', App\Action\Claim\ClaimRejectAction::class, ['GET', 'POST'], 'claim.reject');
$app->route('/claim/{claimId:\d+}/duplicate', App\Action\Claim\ClaimDuplicateAction::class, ['GET', 'POST'], 'claim.duplicate');
$app->route('/claim/{claimId:\d+}/withdraw', App\Action\Claim\ClaimWithdrawAction::class, ['GET', 'POST'], 'claim.withdraw');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}[/{id:\d+}]', App\Action\Poa\PoaAction::class, ['GET', 'POST'], 'claim.poa');
$app->post('/claim/{id:\d+}/poa/{system:sirius|meris}/none-found', App\Action\Poa\PoaNoneFoundAction::class, 'claim.poa.none.found');
$app->route('/claim/{claimId:\d+}/poa/{system:sirius|meris}/{id:\d+}/delete', App\Action\Poa\PoaDeleteAction::class, ['GET', 'POST'], 'claim.poa.delete');
$app->route('/claim/search', App\Action\Claim\ClaimSearchAction::class, ['GET', 'POST'], 'claim.search');
$app->get('/claim/search/download', App\Action\Claim\ClaimSearchDownloadAction::class, 'claim.search.download');
$app->route('/claim/{claimId:\d+}/change-outcome', App\Action\Claim\ClaimChangeOutcomeAction::class, ['GET', 'POST'], 'claim.change.outcome');
$app->route('/claim/{claimId:\d+}/reassign', App\Action\Claim\ClaimReassignAction::class, ['GET', 'POST'], 'claim.reassign');
$app->route('/claim/{claimId:\d+}/notified', App\Action\Claim\ConfirmNotifiedAction::class, ['GET', 'POST'], 'claim.confirm.notified');
$app->route('/claim/{claimId:\d+}/contact-details', App\Action\Claim\ClaimContactDetailsAction::class, ['GET', 'POST'], 'claim.contact.details');
$app->route('/phone-claim', App\Action\PhoneClaimAction::class, ['GET', 'POST'], 'phone-claim');
$app->route('/notify', App\Action\NotifyAction::class, ['GET', 'POST'], 'notify');
