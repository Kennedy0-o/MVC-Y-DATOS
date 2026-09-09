# Sagras Restaurant - Guia 4: CRUD, Service y Reglas de Negocio

## Descripcion
Sistema de gestion de pedidos para restobar implementado en CodeIgniter 4.
Esta version cumple con la Guia 4: CRUD completo, reglas de negocio,
validaciones sintacticas/semanticas, clase Service, manejo de excepciones
y flujo funcional completo.

## Arquitectura
```
Vista -> Controller -> Service -> Model -> Base de Datos
```

## Estructura
```
app/
  Config/Routes.php
  Controllers/PedidoController.php
  Services/PedidoService.php        <-- Logica de negocio
  Models/
    PedidoModel.php                 <-- Entidad principal
    DetallePedidoModel.php
    MesaModel.php
    ProductoModel.php
    PagoModel.php
    PreparacionModel.php
    UsuarioModel.php
    CategoriaModel.php
  Views/
    layout/main.php
    pedido/consultar.php
    pedido/formulario.php           <-- Crear y Editar
    pedido/ver.php
    producto/consultar.php
```

## Reglas de Negocio Implementadas
1. **RN-01**: Mesa ocupada no puede recibir nuevo pedido.
2. **RN-02**: Solo productos disponibles en el menu.
3. **RN-03**: No se puede editar pedido entregado/cancelado.
4. **RN-04**: No se puede cancelar pedido ya pagado.
5. **RN-05**: Eliminacion logica (cancelar) en vez de borrado fisico.

## CRUD Completo (Pedido)
| Operacion | Ruta                        | Metodo |
|-----------|-----------------------------|--------|
| Create    | /pedidos/nuevo              | GET    |
| Create    | /pedidos/registrar          | POST   |
| Read      | /pedidos                    | GET    |
| Read      | /pedidos/ver/(:num)        | GET    |
| Update    | /pedidos/editar/(:num)     | GET    |
| Update    | /pedidos/actualizar/(:num) | POST   |
| Delete    | /pedidos/cancelar/(:num)   | GET    |

## Instalacion
1. `composer create-project codeigniter4/appstarter sagras`
2. Copiar archivos del ZIP dentro del proyecto
3. Importar `sagras_db.sql` en MySQL
4. `cp .env.example .env` y configurar credenciales
5. `php spark serve`
6. Acceder a http://localhost:8080/pedidos

## Validaciones
- **Sintacticas**: Campos obligatorios, numericos, longitud maxima.
- **Semanticas**: Mesa disponible, producto existente, estado editable.

## Manejo de Excepciones
- InvalidArgumentException: datos de entrada incorrectos
- RuntimeException: reglas de negocio violadas
- Exception generica: errores tecnicos logueados, mensaje amigable al usuario

## Autor
Arosquipa Ayma Bryan - Universidad Andina del Cusco - 2026-I
