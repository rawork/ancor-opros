<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
	public function worksAction($id = null)
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {

			$category = $this->get('request')->request->getInt('category');
			$person = $this->get('request')->request->get('person');
			$city = $this->get('request')->request->get('city');

			$limit = $this->get('request')->request->getInt('limit', null);
			$page = $this->get('request')->request->getInt('page', 0);

			if ($limit) {
				if ($page > 0) {
					$limit = ($page*$limit).','.$limit;
				}
			}

			$criteria = 'publish=1 AND is_archive<>1 AND position=0 AND nomination=""';

			if ($category > 0) {
				$criteria .= ' AND age_id='.$category;
			}

			if (trim($city) != '') {
				$criteria .= ' AND city LIKE "%'.$city.'%"';
			}

			if (trim($person) != '') {
				$criteria .= ' AND person LIKE "%'.$person.'%"';
			}

//			$this->get('log')->addError($criteria);

			$cacheCriteria = md5($criteria);
			if ($limit) {
				$cacheCriteria .= md5($limit);
			}

			$pictures = null;
//			$total = 0;
			$total = $this->get('container')->count('gallery_picture', $criteria);

//			if ($this->get('cache')->contains('total_'.$cacheCriteria)) {
//				$total = $this->get('cache')->fetch('total_'.$cacheCriteria);
//			} else {
//				$total = $this->get('container')->count('gallery_picture', $criteria);
//				$this->get('cache')->save('total_'.$cacheCriteria, $total);
//			}

			if ($this->get('cache')->contains($cacheCriteria)) {
				$pictures = $this->get('cache')->fetch($cacheCriteria);
			}

			$pictures = null;

			if (!$pictures) {
				$pictures = $this->get('container')->getItems('gallery_picture', $criteria, 'sort,id DESC', $limit, 'id,name,person,city,age,likes,picture,idea', false);

				$this->get('cache')->save($cacheCriteria, $pictures, 3600);
			}

			$user = $this->get('session')->get('gallery_user');

			if (is_array($pictures)) {
				$votes = array();
				if ($user) {
					$sql = 'SELECT id,picture_id,user_id FROM gallery_vote WHERE user_id='.$user['id'];
					$stmt = $this->get('connection')->prepare($sql);
					$stmt->execute();
					$votes0 = $stmt->fetchAll();

					if (is_array($votes0)){
						foreach ($votes0 as $vote) {
							$votes[$vote['picture_id']] = true;
						}
					}
				}


				foreach ($pictures as &$picture) {
					$picture['vote'] = false;
					if (isset($votes[$picture['id']])) {
						$picture['vote'] = true;
					}
					$picture['picture'] = UPLOAD_REF.$picture['picture'];
					$picture['picture_main'] = str_replace('.jpg', '_main.jpg',$picture['picture']);
					$picture['picture_big'] = str_replace('.jpg', '_big.jpg',$picture['picture']);
				}
				unset($picture);
			}

			$response = new JsonResponse();
			$response->setData(array(
				'pictures' => $pictures,
				'total' => $total,
				'vote_disabled' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'vote_disabled'),
				'gallery_disabled' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'gallery_disabled'),
			));

			return $response;
		}

		$ages = $this->get('container')->getItems('gallery_age', 'publish=1');
		$firstAge = reset($ages);
		$action = 'works';
		$link = $this->generateUrl('public_page_dinamic', array('node' => 'pictures', 'action' => 'archive'));
		$button = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_archive_title');

		$buttons = array(
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_index_title'),
				'link' => '/pictures',
			),
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_archive_title'),
				'link' => '/pictures/archive',
			),
		);

		$gallery_title = 'Работы участников 10 конкурса';

//		$this->get('log')->addError(json_encode($_COOKIE));
//		var_dump($this->get('session')->get('gallery_user'));

		$currentPicture = false;

		if ($id) {

			$picture = $this->get('container')->getItem('gallery_picture', $id);

			if ($picture) {

				$currentPicture = $picture['id'];
				//set active category
				$firstAge = $this->get('container')->getItem('gallery_picture', $picture['age_id']);

				// set meta og:
				$currentUrl = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$this->get('container')->setVar('og_url', $currentUrl);
				$this->get('container')->setVar('og_title', $picture['name']);
				$this->get('container')->setVar('og_description', implode(', ', array($picture['person'], $picture['age'], $picture['city'])));
				$this->get('container')->setVar('og_image', "http://".$_SERVER['SERVER_NAME'].$picture['picture_value']['extra']['main']['path']);

				return $this->render('gallery/picture.html.twig', compact('picture'));
			} else {
				return $this->redirect('/pictures');
			}

		} else {
			return $this->render('gallery/index.html.twig', compact('ages', 'firstAge', 'action', 'link', 'button', 'currentPicture', 'buttons', 'gallery_title'));
		}


	}

	public function indexAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {
			$criteria = 'publish=1 AND is_archive=0 AND (position>0 OR nomination<>"")';

			$category = $this->get('request')->request->getInt('category');
			$person = $this->get('request')->request->get('person');
			$city = $this->get('request')->request->get('city');

			if ($category > 0) {
				$criteria .= ' AND age_id='.$category;
			}

			if (trim($city) != '') {
				$criteria .= ' AND city="'.$city.'"';
			}

			if (trim($person) != '') {
				$criteria .= ' AND person LIKE("%'.$person.'%")';
			}

			$total = $this->get('container')->count('gallery_picture', $criteria);
			$pictures = $this->get('container')->getItems('gallery_picture', $criteria, 'sort');

			foreach ($pictures as &$picture) {
				$picture['picture'] = UPLOAD_REF.$picture['picture'];
				$picture['picture_main'] = str_replace('.jpg', '_main.jpg',$picture['picture']);
				$picture['picture_big'] = str_replace('.jpg', '_big.jpg',$picture['picture']);
			}
			unset($picture);

			$pictures = array_values($pictures);

			$response = new JsonResponse();
			$response->setData(array(
				'pictures' => $pictures,
				'vote_disabled' => true,
				'total' => $total,
			));

			return $response;
		}

		$ages = $this->get('container')->getItems('gallery_age', 'publish=1');
		$firstAge = reset($ages);
		$action = 'index';
		$link = $this->generateUrl('public_page', array('node' => 'pictures'));
		$button = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_index_title');
		$buttons = array(
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_works_title'),
				'link' => '/pictures/works',
			),
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_archive_title'),
				'link' => '/pictures/archive',
			),
		);
		$gallery_title = 'Работы победителей 10 конкурса';

		return $this->render('gallery/index.html.twig', compact('ages', 'firstAge', 'action', 'button', 'link', 'buttons', 'gallery_title'));
	}

	public function archiveAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {
			$criteria = 'publish=1 AND is_archive=1';

			$category = $this->get('request')->request->getInt('category');
			$person = $this->get('request')->request->get('person');
			$city = $this->get('request')->request->get('city');

			if ($category > 0) {
				$criteria .= ' AND age_id='.$category;
			}

			if (trim($city) != '') {
				$criteria .= ' AND city="'.$city.'"';
			}

			if (trim($person) != '') {
				$criteria .= ' AND person LIKE("%'.$person.'%")';
			}

			$total = $this->get('container')->count('gallery_picture', $criteria);
			$pictures = $this->get('container')->getItems('gallery_picture', $criteria, 'sort');

			foreach ($pictures as &$picture) {
				$picture['picture'] = UPLOAD_REF.$picture['picture'];
				$picture['picture_main'] = str_replace('.jpg', '_main.jpg',$picture['picture']);
				$picture['picture_big'] = str_replace('.jpg', '_big.jpg',$picture['picture']);
			}
			unset($picture);

			$pictures = array_values($pictures);

			$response = new JsonResponse();
			$response->setData(array(
				'pictures' => $pictures,
				'vote_disabled' => true,
				'total' => $total,
			));

			return $response;
		}

		$ages = $this->get('container')->getItems('gallery_age', 'publish=1');
		$firstAge = reset($ages);
		$action = 'archive';
		$isArchive = true;
		$link = $this->generateUrl('public_page', array('node' => 'pictures'));
		$button = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_index_title');
		$buttons = array(
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_index_title'),
				'link' => '/pictures',
			),
			array(
				'title' => $this->getManager('Fuga:Common:Param')->getValue('gallery', 'button_works_title'),
				'link' => '/pictures/works',
			),
		);
		$gallery_title = 'Работы победителей 9 конкурса';

		return $this->render('gallery/index.html.twig', compact('ages', 'firstAge', 'action', 'button', 'link', 'buttons', 'gallery_title', 'isArchive'));
	}

	public function voteAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {

			$user = $this->get('session')->get('gallery_user');
			$pictureId = $this->get('request')->request->getInt('picture');

			if (!$user) {
				$this->get('session')->set('picture_voted', $pictureId);

				return array(
					'voted' => false,
					'redirect' => '/pictures/login',
				);
			}

			if ($user['publish'] == 0) {
				$this->get('session')->set('picture_voted', $pictureId);

				return array(
					'voted' => false,
					'redirect' => '/pictures/profile',
				);
			}

			$voteIsDisabled = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'vote_disabled');
			$votePeriod = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'vote_period');


			$sessionId = $this->get('session')->getId();
			$ip = $_SERVER['REMOTE_ADDR'];
			$time = time()-$votePeriod;
			$date = new \DateTime();
			$date->setTimestamp($time);

			if ($voteIsDisabled) {
				return array(
					'voted' => false,
					'error' => 'Голосование закрыто'
				);
			}

			if ($this->get('session')->has('picture_voted')){
				$this->get('session')->remove('picture_voted');
			}

			$vote = $this->get('container')->getItem('gallery_vote', 'picture_id='.$pictureId.' AND user_id='.$user['id']);
			if($vote) {

				$this->get('container')->getTable('gallery_vote')->delete('id='.$vote['id']);

				$likes = $this->get('container')->count('gallery_vote', 'publish=1 AND picture_id='.$pictureId);

				$this->get('container')->updateItem(
					'gallery_picture',
					array('likes' => $likes),
					array('id' => $pictureId)
				);

				return array(
					'voted' => true,
					'message' => 'Вы уже голосовали за эту работу.',
					'likes' => $likes,
					'like' => false,
				);
			} else {
				$this->get('container')->addItem('gallery_vote', array(
					'picture_id' => $pictureId,
					'user_id'    => $user['id'],
					'session_id' => $sessionId,
					'ip_address' => $ip,
					'publish'    => 1,
				));

				$likes = $this->get('container')->count('gallery_vote', 'publish=1 AND picture_id='.$pictureId);

				$this->get('container')->updateItem(
					'gallery_picture',
					array('likes' => $likes),
					array('id' => $pictureId)
				);

				return array(
					'voted' => true,
					'message' => 'Спасибо. Ваш голос учтен.',
					'likes' => $likes,
					'like' => true,
				);
			}
		}

		return $this->redirect($this->generateUrl('public_page' ,array('node' => 'pictures')), 301);
	}

	public function votesAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {
			$user = $this->get('session')->get('gallery_user');

			if (!$user) {
				return array(
					'votes' => false,
					'message' => 'You are now authorized',
				);
			}

			$sql = 'SELECT id,picture_id,user_id FROM gallery_vote WHERE user_id='.$user['id'];
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->execute();

			return array(
				'votes' => $stmt->fetchAll(),
				'message' => 'OK',
			);
		}

		return $this->redirect('/pictures');
	}

	public function loginAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD']) {

			$email = $this->get('request')->request->get('login');
			$password = $this->get('request')->request->get('password');
			if (empty($email) || empty($password)) {
				return array(
					'status' => false,
					'message' => 'Заполните E-mail и пароль',
				);
			}

			$passwordHash = md5($password);

			$sql = "SELECT *  FROM gallery_user WHERE email= :login AND password= :password LIMIT 1";
			$stmt = $this->get('connection')->prepare($sql);
			$stmt->bindValue("login", $email);
			$stmt->bindValue("password", $passwordHash);
			$stmt->execute();
			$user = $stmt->fetch();

//			$this->get('log')->addError(json_encode($user));

			if (!$user) {
				return array(
					'status' => true,
					'task' => 'message',
					'message' => $this->render('gallery/nologin.html.twig'),
				);
			}

			$token = md5($user['email'].$user['password']);

			unset($user['password']);
			$this->get('session')->set('gallery_user', $user);

			return array(
				'status' => true,
				'task' => 'close',
				'message' => 'Вы авторизованы',
				'picture' => $this->get('session')->get('picture_voted'),
			);
		}


		$vk = $this->get('vk')->url();
		$fb = $this->get('fb')->url();

		return $this->render('gallery/login.html.twig', compact('vk', 'fb'));
	}

	public function registerAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD']) {

			$email = $this->get('request')->request->get('login');
			$password = $this->get('request')->request->get('password');
			$password2 = $this->get('request')->request->get('password_again');

			if (empty($email) || empty($password) || empty($password2)) {
				return array(
					'status' => false,
					'message' => 'Заполните E-mail и пароль',
				);
			}

			if (!$this->get('util')->isEmail($email)) {
				return array(
					'status' => false,
					'message' => 'Некорректный формат e-mail',
				);
			}

			if ($password != $password2) {
				return array(
					'status' => false,
					'message' => 'Пароли не совпадают',
				);
			}

			$user = $this->get('container')->getItem('gallery_user', 'email="'.$email.'"');
			if ($user) {
				return array(
					'status' => false,
					'message' => 'Данный e-mail уже зарегистрирован.',
				);
			}

			$code = '';
//			$code = $this->get('util')->genKey(32);

			try {
				$userId = $this->get('container')->addItem('gallery_user', array(
					'email' => $email,
					'password' => md5($password),
					'age' => '',
					'name' => '',
					'lastname' => '',
					'code' => $code,
				));

				$user = $this->get('container')->getItem('gallery_user', $userId);

				$this->get('session')->set('gallery_user', $user);

				$server_link = 'http://'.$_SERVER['SERVER_NAME'];

				$this->get('mailer')->send(
					'Регистрация на сайте dreamcar.toyota.ru',
					$this->render('mail/register.html.twig', compact('server_link', 'code')),
					$email
				);

			} catch (\Exception $e) {
				$this->get('log')->addError($e->getMessage());
				return array(
					'status' => false,
					'message' => 'Ошибка при регистрации пользователя. Напишите нам на info@toyota.ru',
				);
			}

			return array(
				'status' => true,
				'task' => 'redirect',
				'redirect' => '/pictures/profile',
			);
		}

		$vk = $this->get('vk')->url();
		$fb = $this->get('fb')->url();

		return $this->render('gallery/register.html.twig', compact('vk', 'fb'));
	}

	public function profileAction()
	{
		$user = $this->get('session')->get('gallery_user');
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()){

			if (!$user) {
				return array(
					'status' => true,
					'reload' => true,
				);
			}

			try {
				$name = $this->get('request')->request->get('name');
				$lastname = $this->get('request')->request->get('lastname');
				$age = $this->get('request')->request->getInt('age');
				$code = $this->get('request')->request->get('code');

				$emptyFields = array();

				if (empty($name)) {
					$emptyFields[] = 'Имя';
				}

				if (empty($lastname)) {
					$emptyFields[] = 'Фамилия';
				}

				if (empty($age)) {
					$emptyFields[] = 'Возраст';
				}

				if (count($emptyFields) > 0 ) {
					return array(
						'status' => false,
						'message' => 'Не заполнены обязательные поля:<br> '.implode(', ', $emptyFields),
					);
				}

				if (!is_int($age) || intval($age) < 1) {
					return array(
						'status' => false,
						'message' => 'Поле "Возраст" должно содержать только положительные цифры',
					);
				}

//				if (!$user['social_vk'] && !$user['social_fb']) {
//					if (!$code) {
//						return array(
//							'status' => false,
//							'message' => 'Не заполнено поле "Проверочный код"',
//						);
//					} elseif ($user['code'] != $code) {
//						return array(
//							'status' => false,
//							'message' => 'Некорректный Проверочный код',
//						);
//					}
//
//				}

				$this->get('container')->updateItem(
					'gallery_user',
					array(
						'name' => $name,
						'lastname' => $lastname,
						'age' => $age,
						'is_driver' => $this->get('request')->request->getInt('is_driver', 0),
						'auto_brand' => $this->get('request')->request->get('auto_brand'),
						'auto_model' => $this->get('request')->request->get('auto_model'),
						'is_subscribed' => $this->get('request')->request->getInt('is_subscribed', 0),
						'publish' => 1,
					),
					array('id' => $user['id'])
				);

				$user = $this->get('container')->getItem('gallery_user', $user['id']);
				unset($user['password']);
				$this->get('session')->set('gallery_user', $user);
			} catch (\Exception $e) {
				$this->get('log')->addError($e->getMessage());
				return array(
					'status' => false,
					'message' => 'Ошибка регистрации. Напишите нам  info@toyota.ru.',
				);
			}

			return array(
				'status'  => true,
				'task'    => 'message',
				'message' => 'Благодарим за регистрацию!<br><br>Теперь Вы можете принять участие в голосовании!',
				'picture' => $this->get('session')->get('picture_voted'),
			);
		}

		return $this->render('gallery/profile.html.twig', compact('user'));
	}

	public function forgotAction()
	{
		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {
			$email = $this->get('request')->request->get('login');

			$user = $this->get('container')->getItem('gallery_user', 'email="'.$email.'"');
			if (!$user) {
				return array(
					'status' => true,
					'task' => 'message',
					'message' => $this->render('gallery/noforgot.html.twig'),
				);
			}

			$password = $this->get('util')->genKey(8);

			$this->get('container')->updateItem(
				'gallery_user',
				array('password' => md5($password)),
				array('email' => $email)
			);

			$server_link = 'http://'.$_SERVER['SERVER_NAME'];

			$this->get('mailer')->send(
				'Сброс пароля на сайте '.$_SERVER['SERVER_NAME'],
				$this->render('mail/forgot.html.twig', compact('email', 'password', 'server_link')),
				$email
			);

			return array(
				'status' => true,
				'task' => 'message',
				'message' => 'Спасибо!<br>Новый пароль был выслан на указанный e-mail.',
			);
		}

		return $this->render('gallery/forgot.html.twig');
	}

	public function verifyAction($network)
	{
//		if ('POST' == $_SERVER['REQUEST_METHOD'] && $this->isXmlHttpRequest()) {
//
//			$response = new JsonResponse();
//			$result = $this->get($network)->verify();
//
//			if ($result['status']) {
//				$this->get('session')->set('gallery_user', $result['user_local']);
//			}
//
//			$response->setData($result);
//
//			return $response;
//		}

		$result = $this->get($network)->verify();

		if ($result['status']) {
			$this->get('session')->set('gallery_user', $result['user_local']);

			$server_link = 'http://'.$_SERVER['SERVER_NAME'];

			if ($result['register']) {
				$this->get('mailer')->send(
					'Регистрация на сайте dreamcar.toyota.ru',
					$this->render('mail/register.html.twig', compact('server_link')),
					$result['user_local']['email']
				);
			}
		}


		$maincontent = $result['message'];

		$profile = false;
		$close = false;
		$picture = '';

		if ($result['status'] && $result['register'] ) {
			$picture = $this->get('session')->get('picture_voted');
			$profile = true;
		} else {
			if ($result['status']) {
				$picture = $this->get('session')->get('picture_voted');
			}
			$close = $result['status'];

		}

		$response = new Response();
		$response->setContent($this->render('page.empty.html.twig', compact('maincontent', 'close', 'profile', 'picture')));

		return $response;
	}

	public function userAction()
	{
		$user = $this->get('session')->get('gallery_user');
		$vote_disabled = $this->getManager('Fuga:Common:Param')->getValue('gallery', 'vote_disabled');

		if ($this->isXmlHttpRequest()) {
			return array(
				'content' => $this->render('gallery/user.html.twig', compact('user', 'vote_disabled')),
			);
		} else {
			return $this->render('gallery/user.html.twig', compact('user', 'vote_disabled'));
		}
	}

	public function logoutAction()
	{
		$this->get('session')->remove('gallery_user');

		return $this->redirect('/pictures', 301);
	}

	public function regmailAction()
	{
		$server_link = 'http://'.$_SERVER['SERVER_NAME'];
		$isWeb = true;

		$response = new Response();
		$response->setContent($this->render('mail/register.html.twig', compact('server_link', 'isWeb')));

		return $response;
	}

	public function repairAction() {

//		UPDATE gallery_picture SET publish=1 WHERE is_archive=0

		return $this->redirect('/pictures');

		set_time_limit(0);

		$sql = "SELECT id, picture FROM gallery_picture WHERE is_archive=0 AND picture NOT LIKE '/gallery/%'";

		$stmt = $this->get('connection')->prepare($sql);
		$stmt->execute();
		$pictures = $stmt->fetchAll();

		$max = 30;
		$i = 200;
		$j = 1;

		$basedir = PRJ_DIR.'/upload';
		$text = '';

		foreach ($pictures as $picture) {

			$folder = '/g'.sprintf("%'.04d", strval($i));

			@mkdir($basedir.'/gallery'.$folder, 0755, true);

			$path = $picture['picture'];

			$realpath = $basedir.$path;
			$realpath2 = $basedir.str_replace('.jpg', '_main.jpg', $path);
			$realpath3 = $basedir.str_replace('.jpg', '_big.jpg', $path);
			$realpath4 = $basedir.str_replace('.jpg', '_default.jpg', $path);
			$realpath5 = $basedir.str_replace('.jpg', '_small.jpg', $path);

			$arrayPath = pathinfo($realpath);
			$arrayPath2 = pathinfo($realpath2);
			$arrayPath3 = pathinfo($realpath3);
			$arrayPath4 = pathinfo($realpath4);
			$arrayPath5 = pathinfo($realpath5);

			$newpath = $basedir.'/gallery'.$folder.'/'.$arrayPath['basename'];
			$newpath2 = $basedir.'/gallery'.$folder.'/'.$arrayPath2['basename'];
			$newpath3 = $basedir.'/gallery'.$folder.'/'.$arrayPath3['basename'];
			$newpath4 = $basedir.'/gallery'.$folder.'/'.$arrayPath4['basename'];
			$newpath5 = $basedir.'/gallery'.$folder.'/'.$arrayPath5['basename'];

//			var_dump($newpath, $newpath2, $newpath3);

			$ret = @rename ($realpath, $newpath);
			$ret2 = @rename ($realpath2, $newpath2);
			$ret3 = @rename ($realpath3, $newpath3);
			$ret4 = @rename ($realpath4, $newpath4);
			$ret5 = @rename ($realpath5, $newpath5);

			var_dump($ret, $ret2, $ret3, $ret4, $ret5, str_replace($basedir,'',$newpath));

			$this->get('container')->updateItem(
				'gallery_picture',
				array('picture' => str_replace($basedir,'',$newpath)),
				array('id' => $picture['id'])
			);

			if ($j >=30) {
				$j = 1;
				$i++;
				$text .= 'folder'.$i.' is next'."<br>\n";
			} else {
				$j++;
			}

		}

		$text .= 'ready';

		$response = new Response();
		$response->setContent($text);

		return $response;
	}

	public function likesAction() {
		$sql = 'SELECT picture_id, count(id) as likes FROM gallery_vote WHERE publish=1 GROUP BY picture_id';
		$stmt = $this->get('connection')->prepare($sql);
		$stmt->execute();
		while ($picture = $stmt->fetch()) {
			$this->get('container')->updateItem('gallery_picture', array('likes' => $picture['likes']), array('id' => $picture['picture_id']));
		}

		$text = 'ready';

		$response = new Response();
		$response->setContent($text);

		return $response;
	}
	
}