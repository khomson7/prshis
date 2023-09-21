<?php   
        require_once './include/Session.php';
        //SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session

        require_once './include/DbUtils.php';
        require_once './include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);
        $loginname = $_SESSION['loginname'];

        if($loginname && $an != null) {
            Session::insertSystemAccessLog(json_encode(array(
                'programe'=>'IPD-NURSE-DOCUMENT',
                'action'=>'VIEW',
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));
        }

       
       

?>

<div class="row">
    <div class="col-sm-12">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">เพิ่มเอกสาร</a>
                <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">รวมเอกสาร</a> 
            </div> <!-- style="display: none" -->
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab"><?php require_once 'ipdnurse/ipd-nurse-document_tab_DocumentAdd.php';?></div>
            <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab"><?php require_once 'ipd-document-main.php';?></div>
        </div>
    </div>
</div><br>