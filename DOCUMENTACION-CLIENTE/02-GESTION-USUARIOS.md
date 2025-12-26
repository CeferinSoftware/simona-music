# 👥 Gestión de Usuarios y Roles - Simona Music

## 📋 Índice
1. [¿Qué son los Usuarios y Roles?](#qué-son-los-usuarios-y-roles)
2. [Tipos de Usuarios](#tipos-de-usuarios)
3. [Crear un Usuario Nuevo](#crear-un-usuario-nuevo)
4. [Gestionar Permisos](#gestionar-permisos)
5. [Editar y Eliminar Usuarios](#editar-y-eliminar-usuarios)
6. [Buenas Prácticas](#buenas-prácticas)

---

## ¿Qué son los Usuarios y Roles?

En Simona Music, cada persona que accede al sistema es un **Usuario**. Cada usuario tiene un **Rol** que define qué puede hacer.

**Piensa en esto como:**
- 🏠 **Tu casa**: Simona Music
- 🗝️ **Llaves diferentes**: Roles
- 👤 **Personas con llaves**: Usuarios

Algunas personas tienen la llave maestra (Administrador General), otras solo pueden entrar a ciertas habitaciones (Administrador de Terraza), y otras solo pueden hacer cosas específicas (DJ).

---

## Tipos de Usuarios

### 1. 👑 Administrador General (Super Admin)

**¿Qué puede hacer?**
- ✅ TODO - Control total del sistema
- ✅ Crear y borrar terrazas
- ✅ Crear y borrar usuarios
- ✅ Ver todas las estadísticas
- ✅ Gestionar toda la música

**¿Cuándo usar este rol?**
- Para el dueño del negocio
- Para el gerente general
- Para soporte técnico

**⚠️ IMPORTANTE**: Solo dale este rol a personas de TOTAL confianza.

---

### 2. 🏢 Administrador de Terraza

**¿Qué puede hacer?**
- ✅ Ver y gestionar SU terraza específica
- ✅ Subir y organizar música
- ✅ Crear listas de reproducción
- ✅ Programar horarios
- ✅ Ver estadísticas de su terraza
- ✅ Gestionar peticiones de música
- ✅ Personalizar colores y logo

**NO puede hacer:**
- ❌ Ver otras terrazas
- ❌ Crear usuarios
- ❌ Borrar la terraza

**¿Cuándo usar este rol?**
- Para el encargado de cada local
- Para quien gestiona la música día a día

---

### 3. 🎧 DJ

**¿Qué puede hacer?**
- ✅ Transmitir en vivo
- ✅ Usar la Mesa Virtual (Web DJ)
- ✅ Ver peticiones de música
- ✅ Controlar la cola de reproducción

**¿Cuándo usar este rol?**
- Para DJs que solo transmiten en vivo
- Para DJs de eventos temporales

---

## Crear un Usuario Nuevo

### Paso 1: Acceder a la Gestión de Usuarios

1. Inicia sesión como **Administrador General**
2. En el menú lateral, ve a **Administración**
3. Haz clic en **User Accounts** (Cuentas de Usuario)

Verás una lista de todos los usuarios existentes.

---

### Paso 2: Hacer Clic en "Add User"

En la parte superior derecha, encontrarás un botón azul que dice:
```
+ Add User
```

Haz clic ahí.

---

### Paso 3: Llenar el Formulario

Se abrirá un formulario con varios campos. Vamos a explicar cada uno:

#### 📧 **Email Address** (Obligatorio)
El correo electrónico del usuario. **Este será su nombre de usuario para entrar**.

**Ejemplo**: `juan.perez@example.com`

**⚠️ Importante**: 
- Debe ser un email válido
- No puede repetirse (cada usuario necesita un email único)
- Guárdalo bien porque el usuario lo necesitará para entrar

---

#### 🔒 **Password** (Contraseña)

La contraseña para entrar al sistema.

**Consejos para una buena contraseña**:
- Mínimo 8 caracteres
- Mezcla letras y números
- Incluye al menos una mayúscula

**Ejemplo de buena contraseña**: `MiTerraza2024!`

**❌ Ejemplos de MALAS contraseñas**: `123456`, `password`, `terraza`

---

#### 👤 **Name** (Nombre)

El nombre completo del usuario. Esto es solo para mostrarlo en el sistema.

**Ejemplo**: `Juan Pérez López`

---

#### 📱 **Phone Number** (Opcional)

Número de teléfono del usuario. Útil para contacto, pero no obligatorio.

**Ejemplo**: `+34 612 34 56 78`

---

#### 🎨 **Theme** (Tema Visual - Opcional)

El usuario puede elegir entre tema claro u oscuro:
- **Light** (Claro): Fondo blanco, bueno para el día
- **Dark** (Oscuro): Fondo negro, mejor para la noche
- **Browser Default**: Usa el tema del navegador

---

### Paso 4: Asignar Roles y Permisos

Aquí viene la parte MÁS IMPORTANTE. Debes decidir QUÉ puede hacer este usuario.

#### A) Permisos Globales (Todo el Sistema)

Si marcas **"Administer All"** (Administrar Todo), el usuario será **Administrador General** y tendrá acceso a TODO.

**⚠️ Solo usa esto si confías 100% en la persona**.

Otros permisos globales:
- **View Administration** → Ver el panel de administración
- **Manage Stations** → Crear/editar terrazas
- **Manage Users** → Crear/editar usuarios
- **Manage Settings** → Cambiar configuración del sistema

---

#### B) Permisos por Terraza (Recomendado)

Lo más común es dar permisos SOLO para ciertas terrazas.

**Ejemplo**: Quieres que Juan gestione solo "Terraza Centro":

1. Haz clic en **"Add Station Permission"** (Agregar Permiso de Terraza)
2. Selecciona la terraza: **"Terraza Centro"**
3. Marca los permisos que necesita:

**Para Administrador de Terraza, marca**:
- ✅ View Station Management (Ver gestión)
- ✅ Manage Station Profile (Configuración)
- ✅ Manage Station Media (Subir música)
- ✅ Manage Station Playlists (Listas de reproducción)
- ✅ Manage Station Automation (Programación)
- ✅ View Station Reports (Ver estadísticas)

**Para DJ, marca SOLO**:
- ✅ View Station Management
- ✅ Manage Station Broadcasting (Transmitir)
- ✅ Manage Station Streamers (Configurar stream)

---

### Paso 5: Guardar Usuario

Una vez que hayas llenado todo:

1. Revisa que el **email** sea correcto
2. Revisa que los **permisos** sean los correctos
3. Haz clic en **"Save Changes"** (Guardar Cambios)

¡Listo! El usuario ya puede entrar con su email y contraseña.

---

## Gestionar Permisos

### ¿Qué hace cada permiso de terraza?

Vamos a verlos uno por uno para que entiendas exactamente qué permite cada cosa:

#### 🔹 **View Station Management**
- Ver el dashboard de la terraza
- Necesario para CUALQUIER acceso a la terraza
- **Dáselo a todos los usuarios de esa terraza**

#### 🔹 **View Station Reports**
- Ver estadísticas
- Ver historial de reproducción

#### 🔹 **View Station Logs**
- Ver logs técnicos
- Ver errores del sistema
- **Solo para técnicos o administradores**

#### 🔹 **Manage Station Profile**
- Cambiar nombre de la terraza
- Cambiar zona horaria
- Configuración general
- **Para administradores de terraza**

#### 🔹 **Manage Station Broadcasting**
- Configurar transmisión
- Habilitar/deshabilitar DJ en vivo
- **Para administradores y DJs**

#### 🔹 **Manage Station Streamers**
- Crear cuentas de DJ
- Configurar horarios de DJ
- **Para administradores de terraza**

#### 🔹 **Manage Station Mounts**
- Configurar puntos de transmisión
- **Solo para técnicos**

#### 🔹 **Manage Station Remotes**
- Configurar relays remotos
- **Solo para técnicos**

#### 🔹 **Manage Station Media**
- Subir música
- Editar información de canciones
- Organizar carpetas
- **Para administradores de terraza**

#### 🔹 **Delete Station Media**
- BORRAR música
- ⚠️ **Cuidado**: Es permanente
- **Solo para administradores de confianza**

#### 🔹 **Manage Station Automation**
- Crear listas de reproducción
- Programar horarios
- **Para administradores de terraza**

#### 🔹 **Manage Station Web Hooks**
- Integraciones técnicas
- **Solo para técnicos**

#### 🔹 **Manage Station Podcasts**
- Gestionar podcasts
- **Si usas podcasts**

---

## Editar y Eliminar Usuarios

### Editar un Usuario Existente

1. Ve a **Administración → User Accounts**
2. Busca el usuario en la lista
3. Haz clic en el botón **"Edit"** (Editar) - ícono de lápiz
4. Modifica lo que necesites
5. **Save Changes**

**Puedes cambiar**:
- ✅ Nombre
- ✅ Teléfono
- ✅ Permisos
- ✅ Contraseña (si haces clic en "Change Password")

**NO puedes cambiar**:
- ❌ El email (es el identificador único)

---

### Resetear Contraseña

Si un usuario olvidó su contraseña:

1. Edita el usuario
2. En el campo **"New Password"**, escribe la nueva contraseña
3. Haz clic en **"Save Changes"**
4. Dale la nueva contraseña al usuario

**⚠️ IMPORTANTE**: Dísela en persona o por canal seguro, no por email normal.

---

### Eliminar un Usuario

**⚠️ CUIDADO**: Esto es PERMANENTE y no se puede deshacer.

1. Ve a **Administración → User Accounts**
2. Busca el usuario
3. Haz clic en el botón **"Delete"** (Borrar) - ícono de basura
4. Confirma la eliminación

**¿Qué pasa cuando borras un usuario?**
- ❌ Ya no puede entrar al sistema
- ✅ La música que subió NO se borra
- ✅ Las listas que creó NO se borran
- ✅ Todo su trabajo permanece

---

## Buenas Prácticas

### ✅ Seguridad

1. **Una cuenta por persona**: Nunca compartan contraseñas
2. **Principio del mínimo privilegio**: Solo da los permisos que realmente necesita
3. **Contraseñas fuertes**: Exige contraseñas seguras
4. **Revisa regularmente**: Cada mes, revisa quién tiene acceso

### ✅ Organización

1. **Nombres claros**: Usa nombres reales, no apodos
2. **Email corporativo**: Si tienes, mejor email de la empresa
3. **Documenta**: Guarda en un lugar seguro quién tiene qué permisos

### ✅ Roles Recomendados según Uso

#### Para una Cadena de Terrazas:
```
👑 Dueño → Administrador General
👑 Gerente General → Administrador General
🏢 Encargado Terraza A → Admin de Terraza A
🏢 Encargado Terraza B → Admin de Terraza B
🎧 DJ Freelance → DJ en Terrazas A y B
```

#### Para una Sola Terraza:
```
👑 Dueño → Administrador General
🏢 Encargado → Administrador de Terraza
🎧 DJs → Solo permiso de Broadcasting
```

---

## Ejemplos Prácticos

### Ejemplo 1: Crear Administrador de Terraza

**Situación**: Contratas a María para gestionar tu "Terraza Centro"

**Pasos**:
1. **Email**: `maria.garcia@example.com`
2. **Password**: `MariaTerrazaCentro2024!`
3. **Name**: `María García`
4. **Permisos**: 
   - Agregar Terraza Centro
   - Marcar: View, Profile, Media, Delete Media, Automation, Reports

**Resultado**: María puede gestionar todo de Terraza Centro, pero no ve las otras terrazas.

---

### Ejemplo 2: Crear DJ para Fin de Semana

**Situación**: Contratas a DJ Carlos para los viernes y sábados

**Pasos**:
1. **Email**: `djcarlos@example.com`
2. **Password**: `DjCarlos2024!`
3. **Name**: `DJ Carlos`
4. **Permisos**:
   - Agregar tu terraza
   - Marcar SOLO: View, Broadcasting, Streamers

**Resultado**: DJ Carlos solo puede transmitir en vivo, no puede cambiar listas ni configuración.

---

### Ejemplo 3: Crear Técnico de Soporte

**Situación**: Necesitas que un técnico pueda ver los logs de todas las terrazas

**Pasos**:
1. **Email**: `soporte@example.com`
2. **Password**: `Soporte2024!`
3. **Name**: `Soporte Técnico`
4. **Permisos Globales**:
   - Marcar: View Administration
5. **Para cada terraza**:
   - Marcar: View, Logs

**Resultado**: Puede ver logs y diagnosticar problemas, pero no puede cambiar nada.

---

## Preguntas Frecuentes

### ❓ ¿Cuántos usuarios puedo crear?
**Respuesta**: Ilimitados. Crea los que necesites.

### ❓ ¿Puede un usuario tener acceso a múltiples terrazas?
**Respuesta**: ¡SÍ! Simplemente agrega permisos para cada terraza que necesite.

### ❓ ¿Puedo cambiar los permisos después?
**Respuesta**: Sí, en cualquier momento puedes editar el usuario y cambiar sus permisos.

### ❓ ¿Qué pasa si dos personas usan la misma cuenta?
**Respuesta**: Funciona, pero NO es recomendable. No podrás saber quién hizo qué.

### ❓ ¿Hay una forma de ver qué hizo cada usuario?
**Respuesta**: Sí, en **Administración → Audit Log** puedes ver todas las acciones.

### ❓ ¿Puedo desactivar un usuario sin borrarlo?
**Respuesta**: Actualmente no, pero puedes cambiarle la contraseña a algo aleatorio que no sepan.

---

## 🎯 Checklist: Crear un Nuevo Usuario

Usa esto como guía rápida cada vez que crees un usuario:

- [ ] ¿Tengo su email correcto?
- [ ] ¿La contraseña es fuerte?
- [ ] ¿Necesita acceso global o solo a ciertas terrazas?
- [ ] ¿Marqué TODOS los permisos que necesita?
- [ ] ¿Marqué SOLO los permisos que necesita? (ni más ni menos)
- [ ] ¿Guardé los cambios?
- [ ] ¿Le envié sus credenciales de forma segura?

---

## 🚨 Troubleshooting (Solución de Problemas)

### Problema: "El usuario no puede entrar"
**Soluciones**:
1. Verifica que el email esté escrito correctamente (sin espacios)
2. Verifica que la contraseña sea correcta
3. Verifica que el usuario tenga al menos el permiso "View"

### Problema: "El usuario entra pero no ve su terraza"
**Soluciones**:
1. Edita el usuario
2. Verifica que tiene permisos en esa terraza específica
3. Asegúrate de marcar "View Station Management"

### Problema: "El usuario puede ver pero no puede hacer nada"
**Soluciones**:
1. Necesitas darle permisos específicos (Profile, Media, etc.)
2. "View" solo permite ver, no hacer cambios

### Problema: "Olvidé la contraseña de un usuario"
**Soluciones**:
1. Como admin, edita el usuario
2. Escribe una nueva contraseña
3. Guarda cambios
4. Dale la nueva contraseña al usuario

---

¡Con esto ya sabes todo sobre gestionar usuarios en Simona Music! 🎉

**Siguiente**: Lee la **Guía 03** para aprender a crear y gestionar terrazas.
