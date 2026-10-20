# Guion y voz del video

Material para el video de dos minutos que explica como funciona el taller.

## Archivos

| Archivo | Que es |
|---|---|
| `guion.json` | La fuente: un bloque por fragmento, con la locucion y que se ve en pantalla. |
| `guion.md` | El guion legible, con los tiempos de entrada de cada bloque. |
| `linea-de-tiempo.md` | Tabla con el inicio y la duracion real de cada bloque. |
| `generar-voz.ps1` | Genera la pista de voz con la voz de Windows y la une con ffmpeg. |

Los archivos de audio (`voz/` y `voz-pista.*`) no se versionan: se regeneran en
un solo comando.

## Generar la voz

```powershell
powershell -ExecutionPolicy Bypass -File video/generar-voz.ps1
```

Deja `video/voz-pista.wav` y `video/voz-pista.mp3` con la pista completa, un
silencio corto entre bloques y las pausas de entrada y cierre. Cada bloque
sintetizado queda suelto en `video/voz/` para poder rearmar el video.

Requiere Windows con la voz `Microsoft Helena Desktop` y `ffmpeg` en el PATH.

## Ajustar el guion

1. Editar la locucion en `guion.json`.
2. Volver a correr `generar-voz.ps1`.
3. El script rehace `guion.md` y `linea-de-tiempo.md` con los tiempos nuevos.

Para cambiar el ritmo de la voz, editar `$sintetizador.Rate` en
`generar-voz.ps1`: `0` es el ritmo normal, `1` va algo mas rapido y `-1` mas
lento.
