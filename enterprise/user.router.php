<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \classes\middleware\ValidateParam as ValidateParam;
use \classes\middleware\ValidateParamURL as ValidateParamURL;
use \classes\SimpleCache as SimpleCache;
use \modules\enterprise\User as User;
use \modules\enterprise\Util as Util;

    // POST api to create new company
    $app->post('/enterprise/user/data/new', function (Request $request, Response $response) {
        $user = new User($this->db);
        $datapost = $request->getParsedBody();
        $user->adminname = $datapost['Adminname'];
        $user->token = $datapost['Token'];
        $user->username = $datapost['Username'];
        $user->branchid = $datapost['BranchID'];
        $body = $response->getBody();
        $body->write($user->register());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('BranchID','1-10','required'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam(['Username','Adminname'],'1-50','required'));

    // POST api to update user
    $app->post('/enterprise/user/data/update', function (Request $request, Response $response) {
        $user = new User($this->db);
        $datapost = $request->getParsedBody();    
        $user->adminname = $datapost['Adminname'];
        $user->token = $datapost['Token'];
        $user->username = $datapost['Username'];
        $user->branchid = $datapost['BranchID'];
        $user->statusid = $datapost['StatusID'];
        $body = $response->getBody();
        $body->write($user->update());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('StatusID','1-11','numeric'))
        ->add(new ValidateParam('BranchID','1-10','required'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam(['Username','Adminname'],'1-50','required'));

    // POST api to delete user
    $app->post('/enterprise/user/data/delete', function (Request $request, Response $response) {
        $user = new User($this->db);
        $datapost = $request->getParsedBody();    
        $user->adminname = $datapost['Adminname'];
        $user->token = $datapost['Token'];
        $user->username = $datapost['Username'];
        $body = $response->getBody();
        $body->write($user->delete());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam(['Username','Adminname'],'1-50','required'));

    // GET api to show all data user pagination registered user
    $app->get('/enterprise/user/data/search/{username}/{token}/{page}/{itemsperpage}/', function (Request $request, Response $response) {
        $user = new User($this->db);
        $user->search = filter_var((empty($_GET['query'])?'':$_GET['query']),FILTER_SANITIZE_STRING);
        $user->username = $request->getAttribute('username');
        $user->token = $request->getAttribute('token');
        $user->page = $request->getAttribute('page');
        $user->itemsPerPage = $request->getAttribute('itemsperpage');
        $body = $response->getBody();
        $body->write($user->searchUserAsPagination());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParamURL('query'));

    // GET api to get verify user is registered
    $app->get('/enterprise/user/data/verify/register/{username}', function (Request $request, Response $response) {
        $username = $request->getAttribute('username');
        $body = $response->getBody();
        if (Util::isUserRegistered($this->db,$username)){
            $body->write('{"status":"success","code":"RS501","result": {"Username": "'.$username.'","Registered":true},"message":"'.classes\CustomHandlers::getreSlimMessage('RS501').'"}');
        } else {
            $body->write('{"status":"error","code":"RS601","result": {"Username": "'.$username.'","Registered":false},"message":"'.classes\CustomHandlers::getreSlimMessage('RS601').'"}');
        }
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to get verify user is exists
    $app->get('/enterprise/user/data/verify/exists/{username}', function (Request $request, Response $response) {
        $username = $request->getAttribute('username');
        $body = $response->getBody();
        if (Util::isMainUserExist($this->db,$username)){
            $body->write('{"status":"success","code":"RS501","result": {"Username": "'.$username.'","Exists":true},"message":"'.classes\CustomHandlers::getreSlimMessage('RS501').'"}');
        } else {
            $body->write('{"status":"error","code":"RS601","result": {"Username": "'.$username.'","Exists":false},"message":"'.classes\CustomHandlers::getreSlimMessage('RS601').'"}');
        }
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to get data branchid user
    $app->get('/enterprise/user/data/branch/{username}', function (Request $request, Response $response) {
        $username = strtolower($request->getAttribute('username'));
        $branch = Util::getUserBranchID($this->db,$username);
        $body = $response->getBody();
        if (!empty($branch)){
            $body->write('{"status":"success","code":"RS501","result": {"Username": "'.$username.'","BranchID": "'.$branch.'"},"message":"'.classes\CustomHandlers::getreSlimMessage('RS501').'"}');
        } else {
            $body->write('{"status":"error","code":"RS601","result": {"Username": "'.$username.'","BranchID": "'.$branch.'"},"message":"'.classes\CustomHandlers::getreSlimMessage('RS601').'"}');
        }
        
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to show all data status user
    $app->get('/enterprise/user/data/status/{token}', function (Request $request, Response $response) {
        $user = new User($this->db);
        $user->token = $request->getAttribute('token');
        $body = $response->getBody();
        $body->write($user->showOptionStatus());
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to get all data user for statistic purpose
    $app->get('/enterprise/user/stats/data/summary/{username}/{token}', function (Request $request, Response $response) {
        $user = new User($this->db);
        $user->token = $request->getAttribute('token');
        $user->username = $request->getAttribute('username');
        $body = $response->getBody();
        $body->write($user->statUserSummary());
        return classes\Cors::modify($response,$body,200);
    });