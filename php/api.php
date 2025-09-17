<?php
header('Content-Type: application/json; charset=utf-8');
$latest = [];
for ($=1;$i<=6;$i++){
  $latest[] = [
    "title" => "Breaking: Headline $i goes here",
    "by" => "Reporter $i",
    "excerpt" => "Concise summary describing the key details of the story and why it matters.",
    "time" => rand(1,9) . "h",
    "img" => "News/vs.code/assets/images/hero-".(($i%3)+1).".svg"
  ];
}
$trending = [];
for ($i=1;$i<=7;$i++){
  $trending[] = [
    "title" => "Trending topic $i",
    "by" => "Echo Staff",
    "img" => "News/vs.code/assets/images/card-".(in_array($i,[2,6])?"biz":(in_array($i,[3,5])?"tech":(in_array($i,[4])?"sports":"world"))).".svg"
  ];
}
$calendar = ["Mon – 2 fixtures","Tue – 5 fixtures","Wed – 1 fixture","Thu – 3 fixtures","Fri – 4 fixtures"];
echo json_encode(["latest"=>$latest,"trending"=>$trending,"calendar"=>$calendar], JSON_PRETTY_PRINT);
?>
