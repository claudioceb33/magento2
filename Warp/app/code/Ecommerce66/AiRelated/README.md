# Ecommerce66_AiRelated

Módulo que genera automáticamente productos relacionados usando recomendaciones de IA del módulo `Ecommerce66_AiCore`.

---

## Funcionalidad

- **Procesamiento incremental**: procesa el catálogo por lotes, guardando el progreso en base de datos.
- **Resistente a cache:flush**: el progreso se guarda en `core_config_data`, no en caché volátil.
- **Dos modos**: normal (respeta enlaces existentes) o force (reemplaza enlaces relacionados).
- **Comandos CLI**: para ejecución manual o cron.
- **Lock automático**: previene ejecuciones concurrentes en modo "all".
- **Throttling configurable**: pausa entre batches para evitar rate limits de API.

---

## Configuración Admin

**Ruta**: `Stores > Configuration > Ecommerce66 > AI Core > Related Products`

| Campo | Descripción | Default |
|-------|-------------|---------|
| **Enable** | Activa/desactiva el módulo | Yes |
| **Default Limit** | Cantidad de recomendaciones por SKU | 10 |
| **Cache TTL** | Minutos de validez del caché de respuestas AI | 1440 (24h) |
| **Enable Cron Generation** | Activa/desactiva la ejecución automática por cron | No |
| **Cron Batch Size** | Tamaño de lote para cada ejecución del cron | 50 |
| **Enable info logging** | Activa logs informativos (reduce ruido si está en No) | Yes |
| **Enable Generate-All Command** | Permite usar el comando `generate-related-all` | Yes |
| **Generate-All Batch Size** | Tamaño de lote por defecto para comando "all" | 100 |
| **Generate-All Throttle (seconds)** | Pausa entre batches (0 = sin pausa, evita API rate limits) | 1 |

**Nota**: el progreso se persiste en DB (`ecommerce66_ai/ai_related/last_entity_id`), por lo que **no se pierde** con `cache:flush`.

---

## Comandos CLI

### 1. `ecommerce66:ai:generate-related`

Procesa un **único batch** de productos.

#### Opciones

| Opción | Tipo | Default | Descripción |
|--------|------|---------|-------------|
| `--batch` | int | 50 | Cantidad de productos a procesar |
| `--start-id` | int | null | Entity ID desde donde comenzar (opcional) |
| `--force` | flag | false | Forzar reemplazo de enlaces relacionados existentes |
| `--reset` | flag | false | Resetear progreso (comenzar desde entity_id=0) |

#### Ejemplos

```bash
# Procesar 50 productos desde el último punto guardado
php bin/magento ecommerce66:ai:generate-related --batch=50

# Procesar 100 productos forzando reemplazo de enlaces existentes
php bin/magento ecommerce66:ai:generate-related --batch=100 --force

# Resetear progreso y procesar desde cero
php bin/magento ecommerce66:ai:generate-related --batch=20 --reset

# Comenzar desde un entity_id específico
php bin/magento ecommerce66:ai:generate-related --batch=50 --start-id=5000
```

#### Comportamiento

- **Modo normal** (sin `--force`): solo procesa productos que **no tienen** enlaces relacionados.
- **Modo force** (con `--force`): reemplaza todos los enlaces de tipo `related` existentes con las recomendaciones AI.

---

### 2. `ecommerce66:ai:generate-related-all`

Ejecuta un **loop automático** hasta procesar todo el catálogo.

#### Opciones

| Opción | Tipo | Default | Descripción |
|--------|------|---------|-------------|
| `--batch` | int | 100 (desde admin) | Cantidad de productos por iteración |
| `--force` | flag | false | Forzar reemplazo en todas las iteraciones |
| `--reset` | flag | false | Resetear progreso antes de comenzar |
| `--lock-name` | string | `ecommerce66_ai_generate_all_lock` | Nombre del lock (avanzado) |

#### Ejemplos

```bash
# Procesar todo el catálogo desde el último punto guardado
php bin/magento ecommerce66:ai:generate-related-all --batch=100

# Procesar todo el catálogo forzando reemplazo
php bin/magento ecommerce66:ai:generate-related-all --batch=150 --force

# Resetear y procesar todo desde cero
php bin/magento ecommerce66:ai:generate-related-all --batch=100 --reset --force
```

#### Comportamiento

- Ejecuta múltiples iteraciones hasta que no quedan productos por procesar.
- Usa **Lock Manager** para evitar ejecuciones concurrentes.
- Detecta progreso estancado: si `last_entity_id` no avanza entre iteraciones, aborta.
- **Pausa configurable** entre iteraciones (lee `generate_all_throttle_seconds` de admin config).

---

## Configuración de Cron

### Sistema de Cron de Magento

El módulo incluye un job de cron que se ejecuta **cada 5 minutos** por defecto (configurable en `etc/crontab.xml`).

**Schedule actual**: `*/5 * * * *` (cada 5 minutos)

#### Activar/desactivar cron:

1. Ir a Admin: `Stores > Configuration > Ecommerce66 > AI Core > Related Products`
2. **Enable Cron Generation** = `Yes`
3. **Cron Batch Size** = `50` (o el valor deseado)

#### Requisito del sistema:

Asegurate de tener el cron de Magento corriendo en el servidor:

```bash
# Agregar a crontab del usuario web (ej. www-data)
* * * * * /usr/bin/php /ruta/a/magento/bin/magento cron:run >> /var/log/magento_cron.log 2>&1
```

---

### Cron Manual (Alternativa)

Si prefieres **NO usar el cron interno de Magento**, podés programar directamente los comandos CLI:

#### Opción 1: Proceso Mensual

Procesa todo el catálogo una vez al mes, reemplazando enlaces existentes.

```bash
# Día 1 del mes a las 2 AM
0 2 1 * * /usr/bin/php /ruta/a/magento/bin/magento ecommerce66:ai:generate-related-all --batch=200 --reset --force >> /var/log/ai_related_cron.log 2>&1
```

#### Opción 2: Proceso Continuo

Procesa productos nuevos o sin enlaces cada 30 minutos.

```bash
# Cada 30 minutos
*/30 * * * * /usr/bin/php /ruta/a/magento/bin/magento ecommerce66:ai:generate-related --batch=50 >> /var/log/ai_related_cron.log 2>&1
```

#### Opción 3: Proceso Semanal

Procesa todo el catálogo cada domingo a medianoche.

```bash
# Domingos a las 00:00
0 0 * * 0 /usr/bin/php /ruta/a/magento/bin/magento ecommerce66:ai:generate-related-all --batch=150 --reset >> /var/log/ai_related_cron.log 2>&1
```

---

## Logs y Debugging

### Archivos de Log

| Archivo | Contenido |
|---------|-----------|
| `var/log/ai_related.log` | Requests/responses API AI, procesamiento por SKU |
| `var/log/system.log` | Mensajes info/warning/error del generador |
| `var/log/exception.log` | Stack traces de errores fatales |

### Comandos Útiles

```bash
# Ver progreso actual (último entity_id procesado)
php bin/magento config:show ecommerce66_ai/ai_related/last_entity_id

# Ver últimas 200 líneas del log AI
tail -n 200 var/log/ai_related.log

# Seguir log en tiempo real
tail -f var/log/ai_related.log

# Resetear progreso manualmente
php bin/magento config:set ecommerce66_ai/ai_related/last_entity_id 0

# Ver configuración de throttle
php bin/magento config:show ecommerce66_ai/ai_related/generate_all_throttle_seconds

# Cambiar throttle (0 = sin pausa, 1-10 = pausas recomendadas)
php bin/magento config:set ecommerce66_ai/ai_related/generate_all_throttle_seconds 3
```

---

## Throttling (Control de Rate Limit)

### ¿Qué es?

La pausa entre batches evita saturar la API AI con requests consecutivos.

### Configuración

- **Admin**: `Stores > Config > Ecommerce66 > AI Core > Related Products > Generate-All Throttle (seconds)`
- **Default**: 1 segundo
- **Rango**: 0 = sin pausa, 1-10 = pausas recomendadas

### Cuándo ajustar:

| Valor | Uso Recomendado |
|-------|-----------------|
| `0` | API local o sin límites, catálogos pequeños (<500 productos) |
| `1` | API externa estable, catálogos medianos (500-5000 productos) |
| `2-3` | API con rate limits moderados, catálogos grandes (>5000 productos) |
| `5-10` | API con rate limits estrictos o servidor con recursos limitados |

---

## Metodología de Implementación

### Persistencia del Progreso

- **Ubicación**: tabla `core_config_data`, path `ecommerce66_ai/ai_related/last_entity_id`.
- **Valor**: último `entity_id` procesado (entero).
- **Ventaja**: sobrevive a `cache:flush`, deploys, y reinicios.

### Flujo de Procesamiento

1. Lee `last_entity_id` desde DB (o 0 si es primera ejecución).
2. Carga productos con `entity_id > last_entity_id` ordenados ascendentemente.
3. Procesa hasta `$batch` productos:
   - Consulta API AI para obtener recomendaciones.
   - Filtra SKUs inexistentes.
   - Crea objetos `ProductLink` con tipo `related`.
   - Guarda enlaces (merge o replace según modo).
4. Guarda el `entity_id` máximo procesado en DB.
5. Pausa configurable entre batches (lee `generate_all_throttle_seconds` de admin).
6. Próxima ejecución continúa desde ese punto.

### Modos de Operación

#### Modo Normal

- **Comportamiento**: solo procesa productos que **no tienen** enlaces relacionados.
- **Uso**: añadir recomendaciones AI sin tocar enlaces manuales.
- **CLI**: comando sin flag `--force`.

#### Modo Force

- **Comportamiento**: reemplaza todos los enlaces de tipo `related` existentes.
- **Preserva**: otros tipos de enlaces (up-sell, cross-sell).
- **Uso**: actualizar recomendaciones antiguas o corregir datos.
- **CLI**: comando con flag `--force`.

---

## Seguridad y Robustez

### Protecciones Implementadas

- **Lock Manager**: evita ejecuciones concurrentes del comando `generate-related-all`.
- **Detección de progreso estancado**: aborta si `last_entity_id` no avanza entre iteraciones.
- **Try/catch exhaustivos**: captura errores sin romper el proceso completo.
- **Logging completo**: registra cada error con stack trace para debugging.
- **Skip de SKUs inexistentes**: no falla si la API recomienda un SKU que no existe en catálogo.
- **Throttling configurable**: evita rate limits de API ajustando pausa entre batches.

---

## Troubleshooting

### El comando se queda en loop infinito

**Solución**: el módulo detecta esto automáticamente y aborta. Revisar logs:

```bash
tail -n 100 var/log/system.log | grep "no DB progress detected"
```

### API retorna errores de rate limit

**Solución**: aumentar throttle en admin o vía CLI:

```bash
php bin/magento config:set ecommerce66_ai/ai_related/generate_all_throttle_seconds 3
```

### Progreso no avanza

**Solución**: verificar que los productos se están guardando:

```bash
# Ver último entity_id
php bin/magento config:show ecommerce66_ai/ai_related/last_entity_id

# Ejecutar batch pequeño y revisar logs
php bin/magento ecommerce66:ai:generate-related --batch=5
tail -n 50 var/log/ai_related.log
```

---

## Soporte

- **Logs**: revisar `var/log/ai_related.log` para ver actividad detallada.
- **Progreso**: usar `config:show ecommerce66_ai/ai_related/last_entity_id` para verificar avance.
- **Errores**: revisar `var/log/exception.log` si el comando falla.
- **Throttling**: ajustar `generate_all_throttle_seconds` según necesidad de la API.
