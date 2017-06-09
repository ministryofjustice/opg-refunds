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

$app->get('/testing/{index}', App\Action\ScratchAction::class, 'testing');

$app->route('/when-were-fees-paid', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when');
$app->route('/when-were-fees-paid/answer', App\Action\WhenFeesPaidAction::class, ['GET'], 'eligibility.when.answer');


$app->route('/apply/contact', App\Action\ContactDetailsAction::class, ['GET', 'POST'], 'apply.contact');
$app->route('/apply/summary', App\Action\SummaryAction::class, ['GET'], 'apply.summary');
$app->route('/apply/account', App\Action\AccountDetailsAction::class, ['GET', 'POST'], 'apply.account');
$app->route('/apply/done', App\Action\DoneAction::class, ['GET'], 'apply.done');
