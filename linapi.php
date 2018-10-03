<?php

//kenxox

class Line extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('jsonapi/clinic_model');
        $this->load->library('tojson');
        $this->load->helper('url');
    }

    public function pushmessage($lineuserid,$message,$check = 'user'){
    	if($check == 'user'){
    		$url = 'https://api.line.me/v2/bot/message/multicast';
    		$textMessageBuilder = '{
              "to": ["'.$lineuserid.'"],
              "messages":[
                  '.$message.'
              ]
          }';
    	}else if($check == 'group'){
    		$url = 'https://api.line.me/v2/bot/message/push';
    		$textMessageBuilder = '{
              "to": "'.$lineuserid.'",
              "messages":[
                  '.$message.'
              ]
          }';
    	}



      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $textMessageBuilder);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer SBQdj8i2ljeEajvQHzdFfgeiVI3wmO0Gxfr6FqjgCxo+hF+GEIbga2V3Qkz/gJrOlGWjudei9eVkuyPc/6bPL3uLn0sz01IDu82NZXCFEEG7Pm3Oo7rgY62wJUj7oqmJKeoRo4MC9poERhida3FpVQdB04t89/1O/w1cDnyilFU='));
      curl_exec($ch);
    }

    public function replymessage(){
      $channelSecret = '70fbc5461890a4e9f8bceb54204eea6b'; // Channel secret string
      $httpRequestBody = 'https://api.line.me/v2/bot/message/reply'; // Request body string
      $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
      $signature = base64_encode($hash);

      $input = json_decode(file_get_contents("php://input"), true);
      $replyToken = $input['events'][0]['replyToken'];
      $messagessent = $input['events'][0]['message']['text'];
      $lineuserid = $input['events'][0]['source']['userId'];
      $groupid = $input['events'][0]['source']['groupId'];

      $postback = '';
      if(!empty($input['events'][0]['postback'])){
        $postback = $input['events'][0]['postback']['data'];
      }

      $textMessageBuilder = array();
      $textMessageBuilder['replyToken'] = $replyToken;
      $textMessageBuilder['messages'] = $this->filtermessage($messagessent,$lineuserid,$groupid,$postback);

      $textMessageBuilder = json_encode($textMessageBuilder);

      $ch = curl_init($httpRequestBody);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $textMessageBuilder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer SBQdj8i2ljeEajvQHzdFfgeiVI3wmO0Gxfr6FqjgCxo+hF+GEIbga2V3Qkz/gJrOlGWjudei9eVkuyPc/6bPL3uLn0sz01IDu82NZXCFEEG7Pm3Oo7rgY62wJUj7oqmJKeoRo4MC9poERhida3FpVQdB04t89/1O/w1cDnyilFU='));

        if (!empty($replyToken)) {
            file_put_contents("linelog/log.txt", file_get_contents("php://input"));
            $result = curl_exec($ch);
        }else{
          file_put_contents("linelog/log.txt", 'error');
        }
    }

    function filtermessage($message,$lineuserid,$groupid,$postback=''){
      $messagereply = array();
      if (strpos($message, 'ทดสอบ') !== false) {
        $messagereply[0]['type'] = "text";
        $messagereply[0]['text'] = "ตรวจสอบนัดหมายของ user ".$lineuserid;
      }else if (strpos($message, 'จองเตียง') !== false) {
        // $messagereply[0]['type'] = "text";
        // $messagereply[0]['text'] = "จอง";
        $buttonmessage = '{
                "type": "template",
                "altText": "ICU BOOKING CONFIRM",
                "template": {
                "type": "buttons",
                "thumbnailImageUrl": "https://medisees.com/V2017/img/bookingicu.png",
                "imageAspectRatio": "rectangle",
                "imageSize": "contain",
                "imageBackgroundColor": "#73C5C2",
                "title": "ICU BOOKING",
                "text": "สวัสดีค่ะคุณหมอ ต้องการจองเตียงผู้ป่วย ICU วันไหนคะ",
                "defaultAction": {
                    "type": "uri",
                    "label": "View detail",
                    "uri": "https://w55x4.app.goo.gl/?link=https://www.medisees.com&apn=com.medisees.medisee&amv=1&ibi=com.medisees.medisee&isi=1171297231"
                },
                "actions": [
                    {
                      "type": "postback",
                      "label": "จองเตียง",
                      "data": "action=buy&itemid=123"
                    }
                ]
              }
            }';
        $messagereply[0] = json_decode($buttonmessage);
      }else if (strpos($message, 'ใกล้ตัว') !== false) {
        // $messagereply[0]['type'] = "text";
        // $messagereply[0]['text'] = "จอง";
        $buttonmessage = '{
                    "type": "template",
          				  "altText": "คลินิกแล็บ",
          				  "template": {
      				      "type": "carousel",
      				      "columns": [
      				          {
      				            "thumbnailImageUrl": "https://example.com/bot/images/item1.jpg",
      				            "imageBackgroundColor": "#FFFFFF",
      				            "title": "this is menu",
      				            "text": "description",
      				            "defaultAction": {
      				                "type": "uri",
      				                "label": "View detail",
      				                "uri": "http://example.com/page/123"
      				            },
      				            "actions": [
      				                {
      				                    "type": "postback",
      				                    "label": "Buy",
      				                    "data": "action=buy&itemid=111"
      				                },
      				                {
      				                    "type": "postback",
      				                    "label": "Add to cart",
      				                    "data": "action=add&itemid=111"
      				                },
      				                {
      				                    "type": "uri",
      				                    "label": "View detail",
      				                    "uri": "http://example.com/page/111"
      				                }
      				            ]
      				          },
      				          {
      				            "thumbnailImageUrl": "https://example.com/bot/images/item2.jpg",
      				            "imageBackgroundColor": "#000000",
      				            "title": "this is menu",
      				            "text": "description",
      				            "defaultAction": {
      				                "type": "uri",
      				                "label": "View detail",
      				                "uri": "http://example.com/page/222"
      				            },
      				            "actions": [
      				                {
      				                    "type": "postback",
      				                    "label": "Buy",
      				                    "data": "action=buy&itemid=222"
      				                },
      				                {
      				                    "type": "postback",
      				                    "label": "Add to cart",
      				                    "data": "action=add&itemid=222"
      				                },
      				                {
      				                    "type": "uri",
      				                    "label": "View detail",
      				                    "uri": "http://example.com/page/222"
      				                }
      				            ]
      				          }
      				      ],
      				      "imageAspectRatio": "rectangle",
      				      "imageSize": "cover"
      				  }
            	}';
        $messagereply[0] = json_decode($buttonmessage);
      }else if (strpos($message, 'ลงทะเบียน') !== false) {
        // $messagereply[0]['type'] = "text";
        // $messagereply[0]['text'] = "จอง";
        $messagereply[0]['type'] = "text";
        $messagereply[0]['text'] = "ตรวจสอบ Group ".$groupid;
      }else if (strpos($message, 'ผลแล็บ') !== false) {
        $buttonmessage = '{
                  "type": "template",
          "altText": "ผลแล็บ",
          "template": {
              "type": "carousel",
              "columns": [';

        $url = site_url('jsonapi/Clinic/get?categoryId=5');
        $contents = file_get_contents($url, false, $context);
        // session_start();
        $contents = json_decode($contents);
        if(!empty($contents)){
          $maxclinic = 5;
          if(count($contents->clinics) < $maxclinic){
            $maxclinic = count($contents->clinics);
          }
          $count = $maxclinic-1;
          for($i=0;$i<$maxclinic;$i++){
            $image = site_url('img/clinic_default.jpg');
            if(!empty($contents->clinics[$i]->imagePath)){
              $image = $contents->clinics[$i]->imagePath;
            }
            $name = '-';
            if(!empty($contents->clinics[$i]->name)){
              if(strlen($contents->clinics[$i]->name) > 93){
                $string = $contents->clinics[$i]->name;

                $string = substr($string, 0, 93);
                $name = substr($string, 0, strrpos($string, ' ')) . "...";
              }else{
                $name = $contents->clinics[$i]->name;
              }
            }

            $detail = '-';
            if(!empty($contents->clinics[$i]->detail)){
              if(strlen($contents->clinics[$i]->detail) > 200){
                $string = $contents->clinics[$i]->detail;

                $string = substr($string, 0, 200);
                $detail = substr($string, 0, strrpos($string, ' ')) . "...";
              }else{
                $detail = $contents->clinics[$i]->detail;
              }
            }
            $buttonmessage .= '
                    {
                      "thumbnailImageUrl": "'.$image.'",
                      "imageBackgroundColor": "#FFFFFF",
                      "title": "'.$name.'",
                      "text": "'.$detail.'",
                      "defaultAction": {
                          "type": "uri",
                          "label": "View detail",
                          "uri": "'.site_url('clinicreview/clinicdetail/').$contents->clinics[$i]->id.'"
                      },
                      "actions": [
                          {
                              "type": "postback",
                              "label": "ดูผลแล็บล่าสุด",
                              "data": "labresult-'.$contents->clinics[$i]->id.'"
                          }
                      ]
                    }';
                    if($i != $count){
                      $buttonmessage .= ',';
                    }
              }
            }
          $buttonmessage .='
              ],
              "imageAspectRatio": "rectangle",
              "imageSize": "cover"
          }
              }';
        $messagereply[0] = json_decode($buttonmessage);
      }else if (strpos($message, 'แพคเกจตรวจสุขภาพ') !== false) {
        $buttonmessage = '{
            	"type": "flex",
            	"altText": "ผลการตรวจสุขภาพ",
            	"contents": {
            		"type": "carousel",
            		"contents": [{
            				"type": "bubble",
            				"hero": {
            					"type": "image",
            					"url": "https://www.medisees.com/V2017/uploads/service/1/Group_2.jpg",
            					"size": "full",
            					"aspectRatio": "2:1",
            					"aspectMode": "cover"
            				},
            				"body": {
            					"type": "box",
            					"layout": "vertical",
            					"contents": [{
            							"type": "text",
            							"text": "บีทีคลินิกเทคนิคการแพทย์",
            							"size": "md",
            							"color": "#4caf50"
            						},
            						{
            							"type": "text",
            							"text": "แพคเกจตรวจสุขภาพที่ 1",
            							"size": "xl"
            						},
            						{
            							"type": "box",
            							"layout": "vertical",
            							"spacing": "md",
            							"margin": "md",
            							"contents": [{
            									"type": "text",
            									"text": "1. ตรวจความสมบูรณ์ของเลือด CBC",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								},
            								{
            									"type": "text",
            									"text": "2. ตรวจระดับน้ำตาลในกระแสเลือด",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								},
            								{
            									"type": "text",
            									"text": "3. ตรวจการทำงานของไต (BUN,Creatine)",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								}
            							]
            						},
            						{
            							"type": "text",
            							"text": "ราคา 1,500 บาท",
            							"size": "md",
            							"color": "#4caf50",
            							"align": "end"
            						}
            					]
            				},
            				"footer": {
            					"type": "box",
            					"layout": "vertical",
            					"contents": [{
            						"type": "button",
            						"style": "primary",
            						"action": {
            							"type": "uri",
            							"label": "รับบริการ",
            							"uri": "https://example.com"
            						}
            					}]
            				}
            			},
            			{
            				"type": "bubble",
            				"hero": {
            					"type": "image",
            					"url": "https://www.medisees.com/V2017/uploads/service/122/Group_9.jpg",
            					"size": "full",
            					"aspectRatio": "2:1",
            					"aspectMode": "cover"
            				},
            				"body": {
            					"type": "box",
            					"layout": "vertical",
            					"contents": [{
            							"type": "text",
            							"text": "บีทีคลินิกเทคนิคการแพทย์",
            							"size": "md",
            							"color": "#4caf50"
            						},
            						{
            							"type": "text",
            							"text": "แพคเกจตรวจสุขภาพที่ 2",
            							"size": "xl"
            						},
            						{
            							"type": "box",
            							"layout": "vertical",
            							"spacing": "md",
            							"margin": "md",
            							"contents": [{
            									"type": "text",
            									"text": "1. ตรวจความสมบูรณ์ของเลือด CBC",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								},
            								{
            									"type": "text",
            									"text": "2. ตรวจระดับน้ำตาลในกระแสเลือด",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								},
            								{
            									"type": "text",
            									"text": "3. ตรวจการทำงานของไต (BUN,Creatine)",
            									"wrap": true,
            									"size": "md",
            									"flex": 1
            								}
            							]
            						},
            						{
            							"type": "text",
            							"text": "ราคา 2,600 บาท",
            							"size": "md",
            							"color": "#4caf50",
            							"align": "end"
            						}
            					]
            				},
            				"footer": {
            					"type": "box",
            					"layout": "vertical",
            					"contents": [{
            						"type": "button",
            						"style": "primary",
            						"action": {
            							"type": "uri",
            							"label": "รับบริการ",
            							"uri": "https://example.com"
            						}
            					}]
            				}
            			}
            		]

            	}
            }';

        $messagereply[0] = json_decode($buttonmessage);
      }else if (strpos($message, 'การนัดหมาย') !== false) {
        $buttonmessage = '{
        	"type": "flex",
        	"altText": "ผลการตรวจสุขภาพ",
        	"contents": {
        		"type": "bubble",
        		"hero": {
        			"type": "image",
        			"url": "https://www.medisees.com/V2017/uploads/gallery/1_20170714170528-640_640.jpg",
        			"size": "full",
        			"aspectRatio": "20:13",
        			"aspectMode": "cover",
        			"action": {
        				"type": "uri",
        				"uri": "http://linecorp.com/"
        			}
        		},
        		"body": {
        			"type": "box",
        			"layout": "vertical",
        			"contents": [{
        					"type": "text",
        					"text": "บีทีคลินิกเทคนิคการแพทย์",
        					"weight": "bold",
        					"size": "xl"
        				},
        				{
        					"type": "box",
        					"layout": "baseline",
        					"margin": "md",
        					"contents": [{
        							"type": "icon",
        							"size": "sm",
        							"url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
        						},
        						{
        							"type": "icon",
        							"size": "sm",
        							"url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
        						},
        						{
        							"type": "icon",
        							"size": "sm",
        							"url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
        						},
        						{
        							"type": "icon",
        							"size": "sm",
        							"url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png"
        						},
        						{
        							"type": "icon",
        							"size": "sm",
        							"url": "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png"
        						},
        						{
        							"type": "text",
        							"text": "4.0",
        							"size": "sm",
        							"color": "#999999",
        							"margin": "md",
        							"flex": 0
        						}
        					]
        				},
        				{
        					"type": "box",
        					"layout": "vertical",
        					"margin": "lg",
        					"spacing": "sm",
        					"contents": [{
        							"type": "box",
        							"layout": "baseline",
        							"spacing": "sm",
        							"contents": [{
        									"type": "text",
        									"text": "คุณมีนัดหมายเพื่อรับบริการตรวจสุขภาพ แพคเกจตรวจสุขภาพที่บีทีคลินิกเทคนิคการแพทย์",
        									"color": "#aaaaaa",
        									"size": "md",
                          "wrap": true,
        									"flex": 5
        								}
        							]
        						},
                    {
        							"type": "box",
        							"layout": "baseline",
        							"spacing": "sm",
        							"contents": [{
        									"type": "text",
        									"text": "วันที่",
        									"color": "#aaaaaa",
        									"size": "sm",
        									"flex": 1
        								},
        								{
        									"type": "text",
        									"text": "12 August 2018",
        									"wrap": true,
        									"color": "#666666",
        									"size": "sm",
        									"flex": 5
        								}
        							]
        						},
        						{
        							"type": "box",
        							"layout": "baseline",
        							"spacing": "sm",
        							"contents": [{
        									"type": "text",
        									"text": "เวลา",
        									"color": "#aaaaaa",
        									"size": "sm",
        									"flex": 1
        								},
        								{
        									"type": "text",
        									"text": "10:00 - 23:00",
        									"wrap": true,
        									"color": "#666666",
        									"size": "sm",
        									"flex": 5
        								}
        							]
        						}
        					]
        				}
        			]
        		},
        		"footer": {
        			"type": "box",
        			"layout": "vertical",
        			"spacing": "sm",
        			"contents": [{
        					"type": "button",
        					"style": "primary",
        					"height": "sm",
        					"action": {
        						"type": "uri",
        						"label": "ยืนยัน",
        						"uri": "https://linecorp.com"
        					}
        				},
        				{
        					"type": "button",
        					"style": "secondary",
        					"height": "sm",
        					"action": {
        						"type": "uri",
        						"label": "เลื่อน",
        						"uri": "https://linecorp.com"
        					}
        				},
        				{
        					"type": "spacer",
        					"size": "sm"
        				}
        			],
        			"flex": 0
        		}
        	}
        }';
        $messagereply[0] = json_decode($buttonmessage);
      }else if ($message == 'ค้นหา') {
        $buttonmessage = '{
            "type": "imagemap",
            "baseUrl": "https://www.medisees.com/V2017/img/clinicimagemap",
            "altText": "ค้นหา",
            "baseSize": {
                "height": 1864,
                "width": 1040
            },
            "actions": [
                {
                    "type": "message",
                    "text": "ค้นหา ทันตกรรม",
                    "area": {
                        "x": 0,
                        "y": 0,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา เสริมความงาม",
                    "area": {
                        "x": 512,
                        "y": 0,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา ยา",
                    "area": {
                        "x": 0,
                        "y": 466,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา แล็บ",
                    "area": {
                        "x": 512,
                        "y": 466,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา ตา",
                    "area": {
                        "x": 0,
                        "y": 932,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา หัวใจ",
                    "area": {
                        "x": 512,
                        "y": 932,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา ผิวหนัง",
                    "area": {
                        "x": 0,
                        "y": 1398,
                        "width": 512,
                        "height": 466
                    }
                },
                {
                    "type": "message",
                    "text": "ค้นหา สมอง",
                    "area": {
                        "x": 512,
                        "y": 1398,
                        "width": 512,
                        "height": 466
                    }
                }
            ]
          }';
        $messagereply[0] = json_decode($buttonmessage);
      }else if (strpos($message, 'ค้นหา ') !== false) {
        $searchbycategory = '';
        if(strpos($message, 'ทันตกรรม') !== false){
          $searchbycategory = '?categoryId=1';
        }else if(strpos($message, 'เสริมความงาม') !== false){
          $searchbycategory = '?categoryId=15';
        }else if(strpos($message, 'ยา') !== false){
          $searchbycategory = '?categoryId=4';
        }else if(strpos($message, 'แล็บ') !== false){
          $searchbycategory = '?categoryId=5';
        }else if(strpos($message, 'ตา') !== false){
          $searchbycategory = '?categoryId=10';
        }else if(strpos($message, 'หัวใจ') !== false){
          $searchbycategory = '?categoryId=3';
        }else if(strpos($message, 'ผิวหนัง') !== false){
          $searchbycategory = '?categoryId=18';
        }else if(strpos($message, 'สมอง') !== false){
          $searchbycategory = '?categoryId=2';
        }

        $buttonmessage = '{
                  "type": "template",
          "altText": "ค้นหาคลินิก",
          "template": {
              "type": "carousel",
              "columns": [';

        $url = site_url('jsonapi/Clinic/get').$searchbycategory;
        $contents = file_get_contents($url, false, $context);
        // session_start();
        $contents = json_decode($contents);
        if(!empty($contents)){
          $maxclinic = 5;
          if(count($contents->clinics) < $maxclinic){
            $maxclinic = count($contents->clinics);
          }
          $count = $maxclinic-1;
          for($i=0;$i<$maxclinic;$i++){
            $image = site_url('img/clinic_default.jpg');
            if(!empty($contents->clinics[$i]->imagePath)){
              $image = $contents->clinics[$i]->imagePath;
            }
            $name = '-';
            if(!empty($contents->clinics[$i]->name)){
              if(strlen($contents->clinics[$i]->name) > 93){
                $string = $contents->clinics[$i]->name;

                $string = substr($string, 0, 93);
                $name = substr($string, 0, strrpos($string, ' ')) . "...";
              }else{
                $name = $contents->clinics[$i]->name;
              }
            }

            $detail = '-';
            if(!empty($contents->clinics[$i]->detail)){
              if(strlen($contents->clinics[$i]->detail) > 150){
                $string = $contents->clinics[$i]->detail;

                $string = substr($string, 0, 150);
                $detail = substr($string, 0, strrpos($string, ' ')) . "...";
              }else{
                $detail = $contents->clinics[$i]->detail;
              }
            }
            $buttonmessage .= '
                    {
                      "thumbnailImageUrl": "'.$image.'",
                      "imageBackgroundColor": "#FFFFFF",
                      "title": "'.$name.'",
                      "text": "'.$detail.'",
                      "defaultAction": {
                          "type": "uri",
                          "label": "View detail",
                          "uri": "'.site_url('clinicreview/clinicdetail/').$contents->clinics[$i]->id.'"
                      },
                      "actions": [
                          {
                              "type": "postback",
                              "label": "ดูรายละเอียดคลินิก",
                              "data": "clinic-'.$contents->clinics[$i]->id.'"
                          }
                      ]
                    }';
                    if($i != $count){
                      $buttonmessage .= ',';
                    }
              }
            }
          $buttonmessage .='
              ],
              "imageAspectRatio": "rectangle",
              "imageSize": "cover"
          }
              }';
        $messagereply[0] = json_decode($buttonmessage);

      }else if (!empty($postback) && strpos($postback, 'labresult') !== false) {
        $buttonmessage = '{
            "type": "flex",
            "altText": "ผลการตรวจสุขภาพ",
            "contents": {
              "type": "bubble",
              "body": {
                "type": "box",
                "layout": "vertical",
                "contents": [
                  {
                    "type": "text",
                    "text": "ผลการตรวจสุขภาพ",
                    "size": "md",
                    "color": "#4caf50"
                  },
                  {
                    "type": "text",
                    "text": "บีทีคลินิกเทคนิคการแพทย์",
                    "size": "xl"
                  },
                  {
                  "type": "box",
                  "layout": "vertical",
                  "spacing": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "วันที่ 16 มีนาคม 2561",
                        "wrap": true,
                        "size": "md",
                        "color": "#9e9e9e",
                        "flex": 1
                      },
                      {
                        "type": "separator",
                        "color": "#cccccc",
                        "margin": "md"
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "horizontal",
                  "spacing": "md",
                  "margin": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "Total cholesterol",
                        "wrap": true,
                        "size": "md",
                        "flex": 2
                      },
                      {
                        "type": "text",
                        "text": "178 mg",
                        "wrap": true,
                        "color": "#4f7950",
                        "size": "md",
                        "align": "end",
                        "flex": 1
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "horizontal",
                  "spacing": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "Triglyceride",
                        "wrap": true,
                        "size": "md",
                        "flex": 2
                      },
                      {
                        "type": "text",
                        "text": "120 mg",
                        "wrap": true,
                        "color": "#4f7950",
                        "size": "md",
                        "align": "end",
                        "flex": 1
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "horizontal",
                  "spacing": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "HDL",
                        "wrap": true,
                        "size": "md",
                        "flex": 2
                      },
                      {
                        "type": "text",
                        "text": "97 mg",
                        "wrap": true,
                        "color": "#4f7950",
                        "size": "md",
                        "align": "end",
                        "flex": 1
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "vertical",
                  "spacing": "md",
                  "margin": "md",
                  "contents": [
                      {
                        "type": "separator",
                        "color": "#cccccc"
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "horizontal",
                  "spacing": "md",
                  "margin": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "Fasting blood sugar",
                        "wrap": true,
                        "size": "md",
                        "flex": 2
                      },
                      {
                        "type": "text",
                        "text": "134 mg",
                        "wrap": true,
                        "color": "#4f7950",
                        "size": "md",
                        "align": "end",
                        "flex": 1
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "vertical",
                  "spacing": "md",
                  "margin": "md",
                  "contents": [
                      {
                        "type": "separator",
                        "color": "#cccccc"
                      }
                    ]
                  },
                  {
                  "type": "box",
                  "layout": "horizontal",
                  "spacing": "md",
                  "margin": "md",
                  "contents": [
                      {
                        "type": "text",
                        "text": "Lab request id",
                        "wrap": true,
                        "size": "md",
                        "color": "#9e9e9e",
                        "flex": 2
                      },
                      {
                        "type": "text",
                        "text": "#123456",
                        "wrap": true,
                        "color": "#9e9e9e",
                        "size": "md",
                        "align": "end",
                        "flex": 1
                      }
                    ]
                  }
                ]
              }
            }
          }';
        $messagereply[0] = json_decode($buttonmessage);
      }else{
        $messagereply[0]['type'] = "text";
        $messagereply[0]['text'] = "Hello user";
      }

      return $messagereply;
    }

    function sendmessage($lineuserid,$check = 'user'){
      $buttonmessage = '{
                "type": "text",
			    "text": "ส่งใน Group '.$lineuserid.'"
            }';

      $this->pushmessage($lineuserid,$buttonmessage,$check);
    }

}
