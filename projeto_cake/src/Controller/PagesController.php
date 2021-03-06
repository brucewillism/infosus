<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Datasource\ConnectionManager;
/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    /**
     * Displays a view
     *
     * @param array ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display(...$path)
    {   

        $unidade = $this->loadModel('HealthUnits');
        $conn = ConnectionManager::get('default');


        if($this->request->getQuery('search')){
            $palavra="%".$this->request->getQuery('search')."%";
            
            // com inner join
            // $sql = "SELECT h.name,h.id from health_units as h 
            // inner join health_units_specialties 
            // on h.id = health_units_specialties.health_unit_id 
            // inner join specialties 
            // on specialties.id = health_units_specialties.specialtie_id  
            // where h.name Like '".$palavra."' 
            // or complete_address LIKE '".$palavra."' 
            // or specialties.name LIKE '".$palavra."'";
            
            // sem o inner join
            $sql = "
            SELECT h.name,h.id from health_units as h  
            where h.name LIKE '".$palavra."' 
            or complete_address LIKE '".$palavra."'";
            
            

            $stmt = $conn->execute($sql);

            $unidades = $stmt->fetchAll('assoc');
            
            $this->set("unidades",$unidades);
             $this->set("palavra",$palavra);

        }
        else{
            $unidade = $this->loadModel('HealthUnits');
            $unidades = $unidade->find('all',
                [
                'limit'=>10,
                'order'=>['HealthUnits.name'=>'ASC']
                ]              
            );
            $this->set("unidades",$unidades);
        }
        

        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
}
