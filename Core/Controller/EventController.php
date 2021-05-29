<?php

namespace Core\Controller;

use Core\Model\Event;
use Core\System\Contract\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Middleware\Auth;
use Core\System\Validator;

class EventController implements IController{

    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("title")->length(1,50)
            ->get("start")->isNotNull()
            ->get("end")->isNotNull()
            ->get("color")->length(1,25);

        $event = Event::create([
            "title" => $request->get("title"),
            "start" => $request->get("start"),
            "end" => $request->get("end"),
            "color" => $request->get("color"),
            "user_id" => (int)$GLOBALS["user"]->get("id"),
        ]);

        Response::json($event->get("id"),200);
    }

	public function update(Request $request, int $id){
		Validator::validateRequest($request)
			->get("title")->length(1,50)
			->get("start")->isNotNull()
			->get("end")->isNotNull()
			->get("color")->length(1,25);

		$event = Event::find($id);

		if($event){
            $event->update([
                "title" => $request->get("title"),
                "start" => $request->get("start"),
                "end" => $request->get("end"),
                "color" => $request->get("color"),
                "user_id" => (int)$GLOBALS["user"]->get("id")
            ]);
        }else{
		    Response::error("Nie można znaleźć obiektu do edycji.",200);
        }

		Response::json($event,200);
	}

    public function getAll(Request $request){
        $events = Event::where("user_id","=",(int)$GLOBALS["user"]->get("id"))->get($request);
        Response::json($events,200);
    }

    public function delete(Request $request, int $id){
        Event::remove($id);
        Response::json(true,200);
    }
}