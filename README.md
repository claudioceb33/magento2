# Ejemplos Magento 2 — Ceb

Colección de módulos y fragmentos con fines didácticos.

## Convenciones

- PHP con PSR-12, cuatro espacios, finales de línea LF y visibilidad explícita.
  Se conservan los nombres de métodos y propiedades heredados de Magento
  (`_construct`, `_prepareColumns`, etc.). La configuración está en `phpcs.xml.dist`.
- Dependencias por constructor y factories para crear modelos. Persistencia mediante
  resource models o repositorios, sin llamadas directas a ObjectManager.
- ACL explícita en controladores administrativos. Las acciones Save e InlineEdit
  de Installments aceptan POST; Delete conserva el flujo de confirmación existente.
- Escape según contexto: HTML, atributos y JSON seguro para scripts.
- Logging mediante `Psr\Log\LoggerInterface`. OrderCancel utiliza ahora el logger
  configurado por Magento; ya no crea `cron_update_orders.log` mediante Zend.
- No agregar `strict_types` ni tipos a firmas heredadas automáticamente: revisar
  los contratos de Magento y los valores recibidos antes de cambiar coerciones.

Estas convenciones siguen las recomendaciones de
[inyección de dependencias de Adobe](https://developer.adobe.com/commerce/php/development/components/dependency-injection/).
La comprobación PSR-12 es una base de estilo, no sustituye al estándar completo
[Magento Coding Standard](https://github.com/magento/magento-coding-standard).

## Validación local

Requiere PHP 8.1, Python 3, Node.js y PHP_CodeSniffer con PSR-12:

```bash
python3 scripts/check.py
# Para otro binario PHP compatible:
PHP_BIN=php8.1 python3 scripts/check.py
# Sólo formato:
phpcs --standard=phpcs.xml.dist
phpcbf --standard=phpcs.xml.dist
```
El script comprueba sintaxis PHP/PHTML y JavaScript, XML bien formado, estilo y
regresiones de escape en las plantillas GTM. No instala módulos ni modifica la
base de datos. La comprobación XML no valida contra los XSD de Magento.

ShippingCustom, OrderCancel y otros ejemplos dependen de extensiones o atributos
específicos.
