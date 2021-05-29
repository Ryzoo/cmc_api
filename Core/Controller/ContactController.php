<?php
	namespace Core\Controller;

	use Core\Model\Conspect;
	use Core\Model\Friend;
	use Core\Model\Permission;
	use Core\Model\SharedConspect;
	use Core\Other;
	use Core\Other\ConspectBuilder\ConspectBuilder;
	use Core\Other\ConspectBuilder\Themes\CMCTheme;
	use Core\Other\ConspectBuilder\Themes\PZPNTheme;
	use Core\System\EmailSender;
	use Core\System\File;
	use Core\System\FileManager;
	use Core\System\Contract\IController;
	use Core\System\Generator;
	use Core\System\Request;
	use Core\System\Response;
	use Core\Middleware\Auth;
	use Core\System\Validator;

	class ContactController implements IController
	{
		public function middleware(Request $request):bool {
			return true;
		}

		public function sendMessageToUs(Request $request)
		{
			Validator::validateRequest($request)
				->get("name")->isNotNull()
				->get("email")->isNotNull()
				->get("title")->isNotNull()
				->get("message")->isNotNull()
				->get("policyCheck")->isNotNull()
				->get("reqCheck")->isNotNull();

			$email = new EmailSender();
			$param = array(
				[
					"name" => "header",
					"content" => "Użytkownik {$request->get("name")} - {$request->get("email")}  napisał: {$request->get("title")}"
				],
				[
					"name" => "main",
					"content" => "{$request->get("message")}"
				],
				[
					"name" => "button_url",
					"content" => "https://centrumklubu.pl"
				],
				[
					"name" => "button_name",
					"content" => "Kliknij, aby przejść do CM"
				]
			);
			$email->sendEmail("biuro@centrumklubu.pl", null, "Nowa wiadomość kontaktowa", $param);

			Response::json(true);
		}
	}