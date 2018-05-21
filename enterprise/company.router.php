<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \classes\middleware\ValidateParam as ValidateParam;
use \classes\middleware\ValidateParamURL as ValidateParamURL;
use \classes\middleware\ApiKey as ApiKey;
use \classes\SimpleCache as SimpleCache;
use \modules\enterprise\Company as Company;

    // POST api to create new company
    $app->post('/enterprise/company/data/new', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $datapost = $request->getParsedBody();
        $company->username = $datapost['Username'];
        $company->token = $datapost['Token'];
        $company->branchid = $datapost['BranchID'];
        $company->name = $datapost['Name'];
        $company->address = $datapost['Address'];
        $company->phone = $datapost['Phone'];
        $company->fax = $datapost['Fax'];
        $company->email = $datapost['Email'];
        $company->owner = $datapost['Owner'];
        $company->pic = $datapost['PIC'];
        $company->tin = $datapost['TIN'];
        $body = $response->getBody();
        $body->write($company->add());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('BranchID','1-10','required'))
        ->add(new ValidateParam('Address','0-250'))
        ->add(new ValidateParam('Phone','1-15','numeric'))
        ->add(new ValidateParam('Fax','0-15','numeric'))
        ->add(new ValidateParam('Email','0-50','email'))
        ->add(new ValidateParam(['Owner','PIC','TIN'],'0-50'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam(['Username','Name'],'1-50','required'));

    // POST api to update company
    $app->post('/enterprise/company/data/update', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $datapost = $request->getParsedBody();    
        $company->username = $datapost['Username'];
        $company->token = $datapost['Token'];
        $company->branchid = $datapost['BranchID'];
        $company->name = $datapost['Name'];
        $company->address = $datapost['Address'];
        $company->phone = $datapost['Phone'];
        $company->fax = $datapost['Fax'];
        $company->email = $datapost['Email'];
        $company->owner = $datapost['Owner'];
        $company->pic = $datapost['PIC'];
        $company->tin = $datapost['TIN'];
        $company->statusid = $datapost['StatusID'];
        $body = $response->getBody();
        $body->write($company->update());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParam('BranchID','1-10','required'))
        ->add(new ValidateParam('Address','0-250'))
        ->add(new ValidateParam('Phone','1-15','numeric'))
        ->add(new ValidateParam('Fax','0-15','numeric'))
        ->add(new ValidateParam('StatusID','1-11','numeric'))
        ->add(new ValidateParam('Email','0-50','email'))
        ->add(new ValidateParam(['Owner','PIC','TIN'],'0-50'))
        ->add(new ValidateParam('Token','1-250','required'))
        ->add(new ValidateParam(['Username','Name'],'1-50','required'));

    // POST api to delete company
    $app->post('/enterprise/company/data/delete', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $datapost = $request->getParsedBody();    
        $company->branchid = $datapost['BranchID'];
        $company->username = $datapost['Username'];
        $company->token = $datapost['Token'];
        $body = $response->getBody();
        $body->write($company->delete());
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to show all data company pagination registered user
    $app->get('/enterprise/company/data/search/{username}/{token}/{page}/{itemsperpage}/', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $company->search = filter_var((empty($_GET['query'])?'':$_GET['query']),FILTER_SANITIZE_STRING);
        $company->username = $request->getAttribute('username');
        $company->token = $request->getAttribute('token');
        $company->page = $request->getAttribute('page');
        $company->itemsPerPage = $request->getAttribute('itemsperpage');
        $body = $response->getBody();
        $body->write($company->searchCompanyAsPagination());
        return classes\Cors::modify($response,$body,200);
    })->add(new ValidateParamURL('query'));

    // GET api to show all data company
    $app->get('/enterprise/company/data/company/{username}/{token}', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $company->username = $request->getAttribute('username');
        $company->token = $request->getAttribute('token');
        $body = $response->getBody();
        $body->write($company->showOptionCompany());
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to show all data status company
    $app->get('/enterprise/company/data/status/{token}', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $company->token = $request->getAttribute('token');
        $body = $response->getBody();
        $body->write($company->showOptionStatus());
        return classes\Cors::modify($response,$body,200);
    });

    // GET api to show all data company pagination public
    $app->map(['GET','OPTIONS'],'/enterprise/company/data/public/search/{page}/{itemsperpage}/', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $company->search = filter_var((empty($_GET['query'])?'':$_GET['query']),FILTER_SANITIZE_STRING);
        $company->page = $request->getAttribute('page');
        $company->itemsPerPage = $request->getAttribute('itemsperpage');
        $body = $response->getBody();
        $response = $this->cache->withEtag($response, $this->etag2hour.'-'.trim($_SERVER['REQUEST_URI'],'/'));
        if (SimpleCache::isCached(3600,["apikey","query"])){
            $datajson = SimpleCache::load(["apikey","query"]);
        } else {
            $datajson = SimpleCache::save($company->searchCompanyAsPaginationPublic(),["apikey","query"]);
        }
        $body->write($datajson);
        return classes\Cors::modify($response,$body,200,$request);
    })->add(new ValidateParamURL('query'))
        ->add(new ApiKey);

    // GET api to get all data page for statistic purpose
    $app->get('/enterprise/company/stats/data/summary/{username}/{token}', function (Request $request, Response $response) {
        $company = new Company($this->db);
        $company->token = $request->getAttribute('token');
        $company->username = $request->getAttribute('username');
        $body = $response->getBody();
        $body->write($company->statCompanySummary());
        return classes\Cors::modify($response,$body,200);
    });