<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\UploadedFile;
use app\models\Categorias;
use app\models\Servicios;
use app\models\CategoriasServicios;
use app\models\UsuariosServicios;
use app\models\Notificaciones;
use app\models\Pedidos;
use app\models\Util;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use app\models\Trazabilidades;
use app\models\Usuarios;
use app\models\Traccar;
use app\models\Paises;
use app\models\Ciudades;

/**
 * EmpresasController implements the CRUD actions for Empresas model.
 */
class AjaxController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action) 
    { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action); 
    }


    public function actionSubirimagencategorias()
    {
        $model = new Categorias();
        $image = UploadedFile::getInstance($model, 'imagen_upload');
        if( $image ){
            $model->imagen = Util::getGenerarPermalink( Yii::$app->security->generateRandomString() ). '.jpg';
            $path = \Yii::getAlias('@webroot') .Yii::$app->params['uploadImages']. $model->imagen;
            $pathweb = \Yii::getAlias('@web') .Yii::$app->params['uploadImages']. $model->imagen;
            if( $image->saveAs($path) ){

                $thumbnail = Image::thumbnail($path, 300, 300);
                $size = $thumbnail->getSize();
                // if ($size->getWidth() < 250 or $size->getHeight() < 150) {
                    $white = Image::getImagine()->create(new Box(300, 300));
                    $thumbnail = $white->paste($thumbnail, new Point(300 / 2 - $size->getWidth() / 2, 300 / 2 - $size->getHeight() / 2));
                // }
                $thumbnail->save(Yii::getAlias($path), ['quality' => 70]);

                $imageData = base64_encode(file_get_contents($path));

                return Json::encode([
                    [
                        'name' => $model->imagen,
                        'size' => $image->size,
                        'url' => $pathweb,
                        'thumbnailUrl' => $path,
                        // 'deleteUrl' => 'image-delete?name=' . $fileName,
                        'deleteType' => 'POST',
                        'base64' => 'data:'.mime_content_type($path).';base64,'.$imageData,
                    ],
                ]);
            }
        }
        return '';
    }

    public function actionSubirimagenservicios()
    {
        $model = new Servicios();
        $image = UploadedFile::getInstance($model, 'imagen_upload');
        if( $image ){
            $model->imagen = Util::getGenerarPermalink( Yii::$app->security->generateRandomString() ). '.jpg';
            $path = \Yii::getAlias('@webroot') .Yii::$app->params['uploadImages']. $model->imagen;
            $pathweb = \Yii::getAlias('@web') .Yii::$app->params['uploadImages']. $model->imagen;
            if( $image->saveAs($path) ){

                $thumbnail = Image::thumbnail($path, 300, 300);
                $size = $thumbnail->getSize();
                // if ($size->getWidth() < 250 or $size->getHeight() < 150) {
                    $white = Image::getImagine()->create(new Box(300, 300));
                    $thumbnail = $white->paste($thumbnail, new Point(300 / 2 - $size->getWidth() / 2, 300 / 2 - $size->getHeight() / 2));
                // }
                $thumbnail->save(Yii::getAlias($path), ['quality' => 70]);

                $imageData = base64_encode(file_get_contents($path));

                return Json::encode([
                    [
                        'name' => $model->imagen,
                        'size' => $image->size,
                        'url' => $pathweb,
                        'thumbnailUrl' => $path,
                        // 'deleteUrl' => 'image-delete?name=' . $fileName,
                        'deleteType' => 'POST',
                        'base64' => 'data:'.mime_content_type($path).';base64,'.$imageData,
                    ],
                ]);
            }
        }
        return '';
    }

    public function actionGetservicio(){
        $out = [];
        if (isset($_REQUEST['serviceID'])) {
            $serviceID = $_REQUEST['serviceID'];

                $cat_id = CategoriasServicios::find()->where(['servicio_id' => $serviceID])->one();

                $servicio =  Servicios::find()->where(['id' => $serviceID])->asArray()->one();

                
                $out = $servicio;
                
                $out["cat_id"] = $cat_id->categoria_id;

                // return Json::encode(['output'=>$out, 'selected'=>'']);
                return Json::encode(['servicio'=>$out]);
        }
    }

    public function actionListadocategorias(){
        $out = [];
        
            $categorias = Categorias::find()
                ->all();

            if(isset($_REQUEST['ordenado'])){
                $categorias = Categorias::find()
                ->orderBy('nombre ASC')
                ->all();
            }

            foreach ($categorias as $cat) {
                // $out [] = ['id'=>$servicio->servicio_id, 'name'=>strip_tags($servicio->servicio->nombre)]; 
                $out [] = ['id'=>$cat->id, 'nombre'=>$cat->nombre, 'imagen'=>$cat->imagen, 'descripcion'=>$cat->descripcion]; 
                // $out [] = ['item'=>'<div class="serv-item" data-id="' . $servicio->servicio_id . '"><img src="' . $servicio->servicio->imagen . '"><span>' . strip_tags($servicio->servicio->nombre) . '</span></div>']; 
            }
            // return Json::encode(['output'=>$out, 'selected'=>'']);
            return Json::encode(['output'=>$out]);
            return;
        return Json::encode(['output'=>'', 'Seleccione'=>'']);
    }

    public function actionListadoasociados(){
        $out = [];
        $rows = 10;
        $offset = 0;

        if (isset($_REQUEST['srv_id'])) {
            $srv_id = $_REQUEST['srv_id'];
            if ($srv_id != null) {
                
                if(isset($_REQUEST['page'])){
                    $offset = ($_REQUEST['page'] * $rows) - $rows;
                }

                $total = UsuariosServicios::find()
                  ->andWhere(['in', 'servicio_id', $srv_id ])
                  ->count();

                $pages = ceil($total / $rows);

                $usuariosLista = UsuariosServicios::find()
                  ->andWhere(['in', 'servicio_id', $srv_id ])
                  ->orderBy(['id' => SORT_ASC])
                  ->limit($rows)
                  ->offset($offset)
                  ->all();

                foreach ($usuariosLista as $usuarioItem) {
                    $usuario = Usuarios::findOne($usuarioItem->usuario_id);
                    $ciudad = Ciudades::findOne($usuario->ciudad_id);
                    // $out [] = ['id'=>$servicio->servicio_id, 'name'=>strip_tags($servicio->servicio->nombre)]; 
                    $out [] = ['id'=>$usuario->id, 'nombre'=> $usuario->nombres, 'ciudad' => $ciudad, 'imagen'=> $usuario->imagen]; 
                    // $out [] = ['item'=>'<div class="serv-item" data-id="' . $servicio->servicio_id . '"><img src="' . $servicio->servicio->imagen . '"><span>' . strip_tags($servicio->servicio->nombre) . '</span></div>']; 
                }
                // return Json::encode(['output'=>$out, 'selected'=>'']);
                return Json::encode([
                    'output'=>$out, 
                    'total' => $total, 
                    'pages' => $pages,
                    'offset' => $offset,
                    'rows' => $rows
                ]);
                return;
            }
        }
        return Json::encode(['output'=>'', 'Seleccione'=>'']);
    }
    
    public function actionListadoservicios(){
        $out = [];
        if (isset($_REQUEST['depdrop_parents'])) {
            $parents = $_REQUEST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents;
                
                $servicios = CategoriasServicios::find()
                  ->andWhere(['in', 'categoria_id', $cat_id ])
                  ->all();

                if(isset($_REQUEST['ordenado'])){
                   $servicios = CategoriasServicios::findBySql('SELECT cs.id, cs.categoria_id, cs.servicio_id FROM categorias_servicios cs, servicios s WHERE cs.categoria_id in(' . $cat_id . ') AND s.id = cs.servicio_id ORDER BY s.nombre')
                    ->all();
                }

                foreach ($servicios as $servicio) {
                    // $out [] = ['id'=>$servicio->servicio_id, 'name'=>strip_tags($servicio->servicio->nombre)]; 
                    $out [] = ['id'=>$servicio->servicio_id, 'parent'=> $cat_id, 'imagen'=> $servicio->servicio->imagen, 'nombre'=> $servicio->servicio->nombre, 'precio'=> $servicio->servicio->total, 'body' => '<img src="' . $servicio->servicio->imagen . '"><span>' . strip_tags($servicio->servicio->nombre) . '</span><btn class="btn btn-vermas btn-sm center-block" data-srv="' . $servicio->servicio_id . '">Conocer más</btn><btn class="btn btn-success btn-sm center-block btn_add_service" data-srv="' . $servicio->servicio_id . '" data-cat="' . $cat_id . '" data-name="' . strip_tags($servicio->servicio->nombre) . '" >Escoger</btn>']; 
                    // $out [] = ['item'=>'<div class="serv-item" data-id="' . $servicio->servicio_id . '"><img src="' . $servicio->servicio->imagen . '"><span>' . strip_tags($servicio->servicio->nombre) . '</span></div>']; 
                }
                // return Json::encode(['output'=>$out, 'selected'=>'']);
                return Json::encode(['output'=>$out]);
                return;
            }
        }
        return Json::encode(['output'=>'', 'Seleccione'=>'']);
    }

    public function actionContarasociados(){
        
        if(isset($_GET['srv_id'])){
            $srv_id = $_GET['srv_id'];
            $servicio = Servicios::findOne($srv_id);
            $count = (new \yii\db\Query())
            ->from('usuarios_servicios')
            ->where(['servicio_id' => $srv_id])
            ->count();

            return Json::encode(['count'=>$count, 'nombre_servicio'=>$servicio->nombre]);
        }else{
            return Json::encode(['count'=>0, 'nombre_servicio'=>$servicio->nombre]);
        }
    }

    public function actionVerasociado(){
        
        if(isset($_REQUEST['api_token'])){

            if(isset($_REQUEST['aso_id']) && $_REQUEST['api_token'] == Yii::$app->params['api_token']){
                $aso_id = $_REQUEST['aso_id'];
                $asociado = Usuarios::find()
                    ->where(['id' => $aso_id])
                    ->select([
                        'id',
                        'imagen',
                        'nombres', 
                        'apellidos', 
                        'estado',
                        'dias_trabajo', 
                        'horarios_trabajo',
                        'observaciones', 
                        'pais_id', 
                        'ciudad_id'
                    ])
                    ->asArray()
                    ->one();

                $asociado['estado'] = Yii::$app->params['estados_genericos'][$asociado['estado']];

                $asociado['pais'] = Paises::findOne($asociado['pais_id']);

                $asociado['ciudad'] = Ciudades::findOne($asociado['ciudad_id']);
                // $asociado = Usuarios::findOne()->one();
                
                return Json::encode($asociado);
            }else{
                return Json::encode(['id'=>null]);
            }
        }else{
            return Json::encode(['id'=>null]);
        }
    }

    public function actionSubirfotografia()
    {
        $model = new Usuarios();
        $image = UploadedFile::getInstance($model, 'imagen_upload');
        if( $image ){
            $model->imagen = Util::getGenerarPermalink( Yii::$app->security->generateRandomString() ). '.jpg';
            $path = \Yii::getAlias('@webroot') .Yii::$app->params['uploadImages']. $model->imagen;
            $pathweb = \Yii::getAlias('@web') .Yii::$app->params['uploadImages']. $model->imagen;
            if( $image->saveAs($path) ){

                $thumbnail = Image::thumbnail($path, 400, 400);
                $size = $thumbnail->getSize();
                // if ($size->getWidth() < 250 or $size->getHeight() < 150) {
                    $white = Image::getImagine()->create(new Box(400, 400));
                    $thumbnail = $white->paste($thumbnail, new Point(400 / 2 - $size->getWidth() / 2, 400 / 2 - $size->getHeight() / 2));
                // }
                $thumbnail->save(Yii::getAlias($path), ['quality' => 75]);

                $imageData = base64_encode(file_get_contents($path));

                return Json::encode([
                    [
                        'name' => $model->imagen,
                        'size' => $image->size,
                        'url' => $pathweb,
                        'thumbnailUrl' => $path,
                        // 'deleteUrl' => 'image-delete?name=' . $fileName,
                        'deleteType' => 'POST',
                        'base64' => 'data:'.mime_content_type($path).';base64,'.$imageData,
                    ],
                ]);
            }
        }
        return '';
    }

    public function actionSubirdocumento( $atributo_upload, $atributo_modelo )
    {
        $model = new Usuarios();
        $file = UploadedFile::getInstance($model, $atributo_upload);
        if( $file ){
            $model->$atributo_modelo = Util::getGenerarPermalink( Yii::$app->security->generateRandomString() ).'.'.$file->getExtension();
            $path = \Yii::getAlias('@webroot') .Yii::$app->params['uploadFiles']. $model->$atributo_modelo;
            $pathweb = \Yii::getAlias('@web') .Yii::$app->params['uploadFiles']. $model->$atributo_modelo;

            if( $file->saveAs($path) ){

                return Json::encode([
                    [
                        'name' => $model->$atributo_modelo,
                        'size' => $file->size,
                        'url' => $pathweb,
                        'thumbnailUrl' => $path,
                        // 'deleteUrl' => 'image-delete?name=' . $fileName,
                        'deleteType' => 'POST',
                    ],
                ]);
            }
        }
        return '';
    }

    public function actionCiudades(){
        $out = [];
        if (isset($_REQUEST['depdrop_parents'])) {
            $parents = $_REQUEST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $adicionales = Ciudades::find()->andWhere( ['pais_id'=>$cat_id] )->all();
                foreach ($adicionales as $adicional) {
                    $out [] = ['id'=>$adicional->id, 'name'=>$adicional->nombre]; 
                }
                return Json::encode(['output'=>$out, 'selected'=>'']);
                return;
            }
        }
        return Json::encode(['output'=>'', 'Seleccione'=>'']);
    }

}
