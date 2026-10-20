<#
    Genera la pista de voz del video a partir de video/guion.json.

    Uso:
        powershell -ExecutionPolicy Bypass -File video/generar-voz.ps1

    Requiere: Windows (voz Microsoft Helena Desktop) y ffmpeg en el PATH.
#>

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Speech

$raiz = Split-Path -Parent $MyInvocation.MyCommand.Path
$carpetaVoz = Join-Path $raiz 'voz'
$guion = Get-Content (Join-Path $raiz 'guion.json') -Raw -Encoding UTF8 | ConvertFrom-Json

if (-not (Test-Path $carpetaVoz)) {
    New-Item -ItemType Directory -Path $carpetaVoz | Out-Null
}

$silencio = Join-Path $carpetaVoz 'silencio.wav'
$pista = Join-Path $raiz 'voz-pista.wav'
$pistaMp3 = Join-Path $raiz 'voz-pista.mp3'

if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) {
    throw 'ffmpeg no esta en el PATH.'
}

# 1. Sintetizar cada bloque por separado.
$sintetizador = New-Object System.Speech.Synthesis.SpeechSynthesizer
$sintetizador.SelectVoice('Microsoft Helena Desktop')
$sintetizador.Rate = 1
$sintetizador.Volume = 100

Write-Host 'Generando la voz de cada bloque...'

foreach ($bloque in $guion) {
    $destino = Join-Path $carpetaVoz ($bloque.id + '.wav')
    $sintetizador.SetOutputToWaveFile($destino)
    $sintetizador.Speak($bloque.texto)
    $sintetizador.SetOutputToNull()
    Write-Host ('  ' + $bloque.id)
}

$sintetizador.Dispose()

# 2. Silencios: uno corto entre bloques, uno largo al principio y otro al final.
$ErrorActionPreference = 'Continue'

$silencio = Join-Path $carpetaVoz 'silencio.wav'
$silencioInicio = Join-Path $carpetaVoz 'silencio-inicio.wav'
$silencioFinal = Join-Path $carpetaVoz 'silencio-final.wav'

& ffmpeg -y -hide_banner -loglevel error -f lavfi -i anullsrc=r=22050:cl=mono -t 0.35 -c:a pcm_s16le $silencio | Out-Null
& ffmpeg -y -hide_banner -loglevel error -f lavfi -i anullsrc=r=22050:cl=mono -t 0.8 -c:a pcm_s16le $silencioInicio | Out-Null
& ffmpeg -y -hide_banner -loglevel error -f lavfi -i anullsrc=r=22050:cl=mono -t 1.5 -c:a pcm_s16le $silencioFinal | Out-Null

# 3. Unir los bloques en una sola pista.
$lista = Join-Path $raiz 'lista.txt'
$lineas = @("file '" + ($silencioInicio -replace '\\', '/') + "'")

for ($i = 0; $i -lt $guion.Count; $i++) {
    $rutaBloque = Join-Path $carpetaVoz ($guion[$i].id + '.wav')
    $lineas += "file '" + ($rutaBloque -replace '\\', '/') + "'"

    if ($i -lt $guion.Count - 1) {
        $lineas += "file '" + ($silencio -replace '\\', '/') + "'"
    }
}

$lineas += "file '" + ($silencioFinal -replace '\\', '/') + "'"

Set-Content -Path $lista -Value $lineas -Encoding ASCII

& ffmpeg -y -hide_banner -loglevel error -f concat -safe 0 -i $lista -c:a pcm_s16le $pista | Out-Null
& ffmpeg -y -hide_banner -loglevel error -i $pista -c:a libmp3lame -b:a 128k $pistaMp3 | Out-Null

Remove-Item $lista -Force

$ErrorActionPreference = 'Stop'

# 4. Duracion de cada bloque (wav mono 16 bits) para la linea de tiempo.
function DuracionWav([string]$ruta) {
    $bytes = (Get-Item $ruta).Length

    return [math]::Round(($bytes - 44) / 44100, 2)
}

function FormatoTiempo([double]$segundos) {
    $minutos = [math]::Floor($segundos / 60)
    $resto = $segundos - ($minutos * 60)

    return [string]::Format([cultureinfo]::InvariantCulture, '{0}:{1:00.0}', $minutos, $resto)
}

$inicio = 0.8
$total = $inicio
$bloques = @()

foreach ($bloque in $guion) {
    $duracion = DuracionWav (Join-Path $carpetaVoz ($bloque.id + '.wav'))
    $bloques += [pscustomobject]@{
        Titulo = $bloque.titulo
        Pantalla = $bloque.pantalla
        Texto = $bloque.texto
        Inicio = $total
        Duracion = $duracion
    }

    $total += $duracion + 0.35
}

# 5. Linea de tiempo.
$md = @('# Linea de tiempo del video', '', 'Guion por bloques con la duracion real de la voz generada.', '')
$md += '| # | Bloque | Inicio | Duracion | Fin |'
$md += '|---|---|---|---|---|'

for ($i = 0; $i -lt $bloques.Count; $i++) {
    $b = $bloques[$i]
    $fin = $b.Inicio + $b.Duracion
    $md += ('| {0} | {1} | {2} | {3} s | {4} |' -f ($i + 1), $b.Titulo, (FormatoTiempo $b.Inicio), ([string]::Format([cultureinfo]::InvariantCulture, '{0:0.0}', $b.Duracion)), (FormatoTiempo $fin))
}

$md += ''
$md += ('**Duracion total de la pista: {0}** (0,8 s de entrada y 1,5 s de cierre)' -f (FormatoTiempo ($total + 1.5)))
$md += ''

Set-Content -Path (Join-Path $raiz 'linea-de-tiempo.md') -Value $md -Encoding UTF8

# 6. Guion legible para grabar o revisar.
$guionMd = @('# Guion del video (2 minutos)', '', 'Locucion por bloques. Cada bloque indica que se ve en pantalla.', '')

for ($i = 0; $i -lt $bloques.Count; $i++) {
    $b = $bloques[$i]
    $guionMd += ('## {0}. {1}' -f ($i + 1), $b.Titulo)
    $guionMd += ''
    $guionMd += ('**Pantalla:** {0}' -f $b.Pantalla)
    $guionMd += ''
    $guionMd += ('**Locucion:** {0}' -f $b.Texto)
    $guionMd += ''
    $guionMd += ('`{0}  ({1} s)`' -f (FormatoTiempo $b.Inicio), ([string]::Format([cultureinfo]::InvariantCulture, '{0:0.0}', $b.Duracion)))
    $guionMd += ''
}

Set-Content -Path (Join-Path $raiz 'guion.md') -Value $guionMd -Encoding UTF8

Write-Host ''
Write-Host ('Pista lista: ' + $pista)
Write-Host ('Version mp3: ' + $pistaMp3)
Write-Host ('Duracion de la pista: ' + (FormatoTiempo ($total + 1.5)))
