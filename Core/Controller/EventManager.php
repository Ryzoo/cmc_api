<?php

namespace Core\Controller;

use Core\Models\Event;
use Core\System\IController;
use Core\System\Request;
use Core\System\Response;
use Core\Models\Animation;
use Core\Middlewares\Auth;
use Core\System\Validator;

class EventManager implements IController{

    public function middleware(Request $request):bool {
        return Auth::check($request);
    }

    public function add(Request $request){
        Validator::validateRequest($request)
            ->get("title")->length(1,50)
            ->get("start")->isNotNull()
            ->get("end")->isNotNull()
            ->get("color")->length(1,25);

        $event = new Event();
        $event->title = $request->get("title");
        $event->start = $request->get("start");
        $event->end = $request->get("end");
        $event->color = $request->get("color");
        $event->user_id = (int)$GLOBALS["user"]->id;
        $event->save();

        return new Response($event->id,200);
    }

	public function update(Request $request){
		Validator::validateRequest($request)
			->get("id")->isNumber()
			->get("title")->length(1,50)
			->get("start")->isNotNull()
			->get("end")->isNotNull()
			->get("color")->length(1,25);

		$event = Event::find((int)$request->get("id"));
		$event->title = $request->get("title");
		$event->start = $request->get("start");
		$event->end = $request->get("end");
		$event->color = $request->get("color");
		$event->user_id = (int)$GLOBALS["user"]->id;
		$event->save();

		return new Response([],200);
	}

    public function all(Request $request){
        $ret = $this->getEvent();
        return new Response($ret,200);
    }

    public function getEvent($id=null){
        $events = Event::where("user_id","=",(int)$GLOBALS["user"]->id)->get();
        $monthCount = array();
        $years = range(date('Y')+5, date('Y')-5);

        foreach ( $years as $year) {
            $monthCount[$year] = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0);

            foreach ($events as $event){
                $monthStart = ((int)date("n",strtotime($event->start)));
                $monthEnd = ((int)date("n",strtotime($event->end)));

                if( date("Y",strtotime($event->start)) == $year){
                    $monthCount[$year][$monthStart-1] ++;
                }

                if($monthEnd !== $monthStart && date("Y",strtotime($event->end)) == $year){
                    $monthCount[$year][$monthEnd-1] ++;
                }
            }
        }

        return array("events"=>$events, "monthCount" => $monthCount);
    }

    public function delete(Request $request){
        Validator::validateRequest($request)
            ->get("id")->isNumber();

        $event = Event::find($request->get("id"));
        $event->delete();

        return new Response(true,200);
    }
}