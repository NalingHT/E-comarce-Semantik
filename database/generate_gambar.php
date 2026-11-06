<?php
// Generator gambar produk SVG - jalankan sekali: php generate_gambar.php
// Menghasilkan ilustrasi produk dengan gradasi warna ke assets/img/produk/

$produk = [
    ['file' => 'headphone.svg',  'icon' => '🎧', 'label' => 'Headphone\nWireless',   'c1' => '#1b5e20', 'c2' => '#43a047'],
    ['file' => 'mouse.svg',      'icon' => '🖱️', 'label' => 'Mouse\nGaming RGB',     'c1' => '#e65100', 'c2' => '#ff9800'],
    ['file' => 'kaos.svg',       'icon' => '👕', 'label' => 'Kaos Polos\nPremium',    'c1' => '#ff8f00', 'c2' => '#ffc107'],
    ['file' => 'matras.svg',     'icon' => '🧘', 'label' => 'Matras Yoga\nAnti Slip', 'c1' => '#2e7d32', 'c2' => '#66bb6a'],
    ['file' => 'keripik.svg',    'icon' => '🍟', 'label' => 'Keripik\nBalado',        'c1' => '#bf360c', 'c2' => '#f57c00'],
    ['file' => 'kopi.svg',       'icon' => '☕', 'label' => 'Kopi Arabika\nGayo',     'c1' => '#3e2723', 'c2' => '#795548'],
    ['file' => 'madu.svg',       'icon' => '🍯', 'label' => 'Madu Murni\nHutan',      'c1' => '#ff6f00', 'c2' => '#ffb300'],
    ['file' => 'sapu.svg',       'icon' => '🧹', 'label' => 'Sapu Lidi\nPremium',     'c1' => '#33691e', 'c2' => '#8bc34a'],
    ['file' => 'keyboard.svg',   'icon' => '⌨️', 'label' => 'Keyboard\nMechanical',   'c1' => '#004d40', 'c2' => '#26a69a'],
    ['file' => 'tas.svg',        'icon' => '👜', 'label' => 'Tas Kulit\nAsli',        'c1' => '#4e342e', 'c2' => '#a1887f'],
];

$dir = __DIR__ . '/../assets/img/produk/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

foreach ($produk as $p) {
    [$baris1, $baris2] = explode('\n', $p['label']);
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="450" viewBox="0 0 600 450">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$p['c1']}"/>
      <stop offset="100%" stop-color="{$p['c2']}"/>
    </linearGradient>
    <radialGradient id="glow" cx="50%" cy="42%" r="45%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="600" height="450" fill="url(#bg)"/>
  <circle cx="520" cy="60" r="90" fill="#ffffff" opacity="0.08"/>
  <circle cx="70" cy="380" r="110" fill="#ffffff" opacity="0.08"/>
  <circle cx="540" cy="400" r="50" fill="#ffffff" opacity="0.06"/>
  <ellipse cx="300" cy="200" rx="200" ry="160" fill="url(#glow)"/>
  <text x="300" y="235" font-size="130" text-anchor="middle">{$p['icon']}</text>
  <rect x="150" y="320" width="300" height="86" rx="16" fill="#000000" opacity="0.25"/>
  <text x="300" y="356" font-family="Segoe UI, Arial, sans-serif" font-size="30" font-weight="700" fill="#ffffff" text-anchor="middle">{$baris1}</text>
  <text x="300" y="392" font-family="Segoe UI, Arial, sans-serif" font-size="30" font-weight="700" fill="#ffffff" text-anchor="middle">{$baris2}</text>
</svg>
SVG;
    file_put_contents($dir . $p['file'], $svg);
    echo "OK: {$p['file']}\n";
}
echo "Selesai.\n";
