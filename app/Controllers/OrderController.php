<?php

namespace Controllers;

//use PHPMailer\PHPMailer\PHPMailer;

class OrderController extends Controller{

    public function orderAction($f3)
    {
        $this->f3->set('ONERROR', function($f3) {
            echo $this->f3->get('ERROR.text');
        });
        $f3->set('current_route', $f3->hive()["PATH"]); //CURRENT ROUTE

        if(!isset($_SESSION['isset_promocode']))
        {
            $_SESSION['count_products'] = 0;
            $_SESSION['total_amount'] = 0;
            if(isset($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $item) {
                if (!empty($item['price_with_discount'])) {
                    $_SESSION['total_amount'] = $_SESSION['total_amount'] + ($item['price_with_discount'] * $item['count']);
                } else {
                    $_SESSION['total_amount'] = $_SESSION['total_amount'] + ($item['price'] * $item['count']);
                }
                $_SESSION['count_products'] = $_SESSION['count_products'] + $item['count'];
            }
            } else {
                header("Location: /");
            }
        }

		if(isset($_COOKIE["user"]))
		{
			$user = json_decode($_COOKIE["user"]);
			$f3->set('user', $user);
		}
        
        
        $f3->set('page_title', 'Оформление заказа');
        $f3->set('description_seo', 'Оформление заказа');
        $f3->set('content', 'order.htm');

        //Получаем другие товары в рекомендательный блок
        $query_products_related = $this->DB->exec('SELECT * FROM `products` ORDER BY views DESC LIMIT 5');
        $f3->set('products_related', $query_products_related);
        
        echo \View::instance()->render('templates/layout.htm');

    }

    

    public function processOrder($f3, $params)
    {
        $this->f3->set('ONERROR', function($f3) {
            echo $this->f3->get('ERROR.text');
        });
        $totalAmount = (float)$_SESSION["total_amount"];
        $name = $f3->get('POST.name');
        $phone = $f3->get('POST.phone');

        //Получаем все ID товаров из корзины
        $products_id = [];
        $pList = "";
        foreach ($_SESSION["cart"] as $product)
        {
            $products_id[] = $product["id"];
        }
        $pList = implode(",", $products_id);

        //Вносим в базу и переходим на оплату

        $order = new \DB\SQL\Mapper($this->DB,'orders');

        $order->phone = $phone;
        $order->name = $name;
        $order->products_id = $pList;
        $order->summ = $totalAmount;
        $order->status = 2;
        $order->created = time();
        if (isset($_SESSION["isset_promocode"])) {
            $order->promocode = $_SESSION["isset_promocode"];
        } else {
            $order->promocode = '';
        }
        
        $order->save();

        $orderID = $order->id;
		
		$user = [
			"name" => $name,
			"phone" => $phone,
		];
		$user = json_encode($user);
		
		setcookie('user', $user, time() + 31536000, "/");
        
        $message = "Payment for order №" . $orderID;

        $_SESSION["total_amount"] = $totalAmount;
        
        $_SESSION["order_id"] = $orderID;
        $f3->reroute("/?success=1");

    }
    /*
    private function send_email($to, $title, $text)
    {
        $mail = new PHPMailer(true);
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->CharSet = "utf-8";
        $mail->Host = "smtp.gmail.com";//Сервер SMTP gmail
        $mail->SMTPAuth = true;
        $mail->Username = "mr.alex.anonymous@gmail.com";//В документации Google указано что логин это адрес вместе с собакой
        $mail->Password = "bzzoqmszkjobmltw";//Пароль
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;//Указываем что TLS
        $mail->Port = 587;
        $mail->setFrom("mr.alex.anonymous@gmail.com", "Магазин");
        $mail->addAddress($to);//Кому отправляем письмо
        $mail->isHTML(false);//Письмо в формате HTML
        $mail->Subject = $title;
        $mail->Body = $text;
        $mail->AltBody = $text;
      
        $mail->send();
        return true;
    }
    */
}

?>