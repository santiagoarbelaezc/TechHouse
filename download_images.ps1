$images = @{
    'headset.jpg'      = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&h=896&q=85'
    'motherboard.jpg'  = 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&h=896&q=85'
    'ram.jpg'          = 'https://images.unsplash.com/photo-1562976540-1502c2145186?auto=format&fit=crop&w=1200&h=896&q=85'
    'ssd.jpg'          = 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=1200&h=896&q=85'
    'chair.jpg'        = 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=1200&h=896&q=85'
    'microphone.jpg'   = 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&h=896&q=85'
    'webcam.jpg'       = 'https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?auto=format&fit=crop&w=1200&h=896&q=85'
    'smartwatch.jpg'   = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&h=896&q=85'
}

foreach ($entry in $images.GetEnumerator()) {
    $outFile = "frontend/src/assets/products/$($entry.Key)"
    Write-Host "Downloading $($entry.Key)..."
    Invoke-WebRequest -Uri $entry.Value -OutFile $outFile
}

Write-Host "All downloads complete."
