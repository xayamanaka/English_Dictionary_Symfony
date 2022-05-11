<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Dictionary;
use App\Entity\History;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\DictionaryRepository;
use App\Repository\HistoryRepository;
use App\Service\SearchImage;

class DictionaryMainController extends AbstractController
{
     /**
     * @Route("/dictionary", name="dictionary")
     */
    public function index(Session $session,HistoryRepository $historyRepository,SearchImage $searchImage): Response
    {
        //sessionでログインユーザー情報を取得
        $lastUsername=$session->get('name');

        //ログイン直後に表示される時にエラーにならないようデータを空にする。
        $word="";  //検索ワード
        $mean="";  //ワードのの意味
        $html="";  //検索先の画像に関するhtml情報
        $searched_image=array(); //取得したhtml情報から表示する画像を配列に入れるため

        //ログインユーザーの検索履歴を検索
        $histories=$historyRepository->findBy(['username'=> $lastUsername]); //usernameで履歴を検索し、データを取得

        //取得した情報をtwigに渡す
        return $this->render('dictionary/index.html.twig', [
            'lastUsername'=>$lastUsername, 'word'=>$word,'mean'=>$mean,'histories'=>$histories,
            'html'=>$html, 'searched_image'=>$searched_image
        ]);
    }

    /**
     * @Route("/search", name="search", methods="POST")
     */
    public function search(Session $session, ManagerRegistry $doctrine, DictionaryRepository $dictionaryRepository, HistoryRepository $historyRepository,SearchImage $searchImage): Response
    {
        $lastUsername=$session->get('name');  //ユーザー情報を取得
        $word=$_POST['word'];                 //twigから情報を取得

        $html=$searchImage->getSearchImage($word);  //Serviceから読み込んだgetSearchImageを使って検索先のhtml情報を取得
        $html= explode(",",$html);  //取得した情報を","で区切って配列に入れる
        $cnt=count($html);          //配列の要素数をカウント
        $needle="1400w";            //取得したい画像を取得するためのkeyword
        $s=0;   //for文を回すための変数
        $searched_image=array();   //配列の定義
        for($i=0; $i<$cnt; $i++){       //for文で各要素をチェック
        if(strpos($html[$i],$needle)===false){  //keywordを含んだ配列があるかチェック
            continue;       //なければコンティニュー
        }else{
            $searched_image[$s]=$html[$i];  //あれば配列$searched_imageに入れる
            if($s<3){            //3枚分を取得
            $s++;}               //取得したら１つ増やして次に。
            else{                
                continue;        //無ければコンティニュー
            }}}

        
        $item = $dictionaryRepository->findOneBy(['word' => $word]);  //キーワードで検索しその情報を取得
        $mean=$item->getMean();   //取得した情報の中からmeanを取得

        //検索履歴をHistoryに追加する
        $entityManager = $doctrine->getManager();   //entityManagerを準備する
        $history = new History();                   //追加するためのデータ枠を作成
        $history->setUsername($lastUsername);       //ユーザーネームをセット
        $history->setWord($word);                   //英単語をセット
        $history->setMean($mean);                   //意味をセット
       
        $entityManager->persist($history);  //指示をセット
        $entityManager->flush();            //指示を完了

        //検索履歴を取得する
        $histories=$historyRepository->findBy(['username'=> $lastUsername]);

        return $this->render('dictionary/index.html.twig', [
            'lastUsername'=>$lastUsername, 'word'=>$word,'mean'=>$mean, 'histories'=>$histories,'searched_image'=>$searched_image
        ]);
    }

    /**
     * @Route("/delete_history", name="delete_history",methods={"POST"})
     */
    public function delateHistory(ManagerRegistry $doctrine): Response
    {
        $id=$_POST['history_id'];
        $entityManager =$doctrine->getManager();
        $history = $entityManager->getRepository(History::class)->find($id);
        if(!$history){
            throw $this->createNotFoundException(
                'No History hare');
        }
        $entityManager->remove($history);
        $entityManager->flush();
        return $this->redirectToRoute('dictionary');
    }

    /**
     * @Route("/add", name="add",methods={"POST"})
     */
    public function createProduct(ManagerRegistry $doctrine): Response
    {
        $add_word=$_POST['add_word'];
        $add_mean=$_POST['add_mean'];
        $entityManager = $doctrine->getManager();

        $item= new Dictionary();
        $item->setWord($add_word);
        $item->setMean($add_mean);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($item);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->render('dictionary/added.html.twig', ['add_word'=>$add_word,'add_mean'=>$add_mean
        ]);
    }

}
