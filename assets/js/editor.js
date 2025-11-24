// Canvas Editor with Fabric.js
let canvas;
let easyModeText = null;

document.addEventListener('DOMContentLoaded', function () {
    initializeCanvas();
    setupEventListeners();
});

function initializeCanvas() {
    // Initialize Fabric.js canvas
    canvas = new fabric.Canvas('canvas', {
        width: TEMPLATE_DATA.width || 800,
        height: TEMPLATE_DATA.height || 600,
        backgroundColor: '#ffffff'
    });

    // Load template image if exists
    if (TEMPLATE_DATA.image_path) {
        loadTemplateImage(TEMPLATE_DATA.image_path);
    } else {
        hideLoadingSpinner();
    }
}

function loadTemplateImage(imagePath) {
    const imgElement = new Image();
    imgElement.crossOrigin = 'anonymous';

    imgElement.onload = function () {
        const fabricImage = new fabric.Image(imgElement);

        // Scale to fit canvas
        const scale = Math.min(
            (canvas.width - 40) / fabricImage.width,
            (canvas.height - 40) / fabricImage.height
        );

        fabricImage.scale(scale);
        fabricImage.set({
            left: canvas.width / 2,
            top: canvas.height / 2,
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false
        });

        canvas.add(fabricImage);
        canvas.sendObjectToBack(fabricImage);

        // Add default text
        addDefaultText();

        canvas.renderAll();
        hideLoadingSpinner();
    };

    imgElement.onerror = function () {
        console.error('Failed to load template image');
        hideLoadingSpinner();
    };

    imgElement.src = imagePath;
}

function addDefaultText() {
    const text = new fabric.IText('اسمك هنا', {
        left: canvas.width / 2,
        top: canvas.height / 2 + 150,
        fontFamily: 'Cairo',
        fontSize: 50,
        fill: '#000000',
        fontWeight: 'bold',
        originX: 'center',
        originY: 'center'
    });

    canvas.add(text);
    canvas.setActiveObject(text);
    easyModeText = text;
    canvas.renderAll();
}

function setupEventListeners() {
    // Easy mode text input
    document.getElementById('easy-mode-text').addEventListener('input', function (e) {
        if (easyModeText) {
            easyModeText.set('text', e.target.value || ' ');
            canvas.renderAll();
        } else {
            addText(e.target.value || 'نص جديد');
        }
    });

    // Add text button
    document.getElementById('add-text-btn').addEventListener('click', function () {
        addText('نص جديد');
    });

    // Text color
    document.getElementById('text-color').addEventListener('change', function (e) {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('fill', e.target.value);
            canvas.renderAll();
        }
    });

    // Font family
    document.getElementById('font-family').addEventListener('change', function (e) {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('fontFamily', e.target.value);
            canvas.renderAll();
        }
    });

    // Font size
    document.getElementById('font-size').addEventListener('input', function (e) {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('fontSize', parseInt(e.target.value));
            canvas.renderAll();
        }
        document.getElementById('font-size-value').textContent = e.target.value;
    });

    // Upload image
    document.getElementById('upload-image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                fabric.Image.fromURL(event.target.result, function (img) {
                    img.scaleToWidth(200);
                    img.set({
                        left: canvas.width / 2,
                        top: canvas.height / 2,
                        originX: 'center',
                        originY: 'center'
                    });
                    canvas.add(img);
                    canvas.setActiveObject(img);
                    canvas.renderAll();
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Download button
    document.getElementById('download-btn').addEventListener('click', function () {
        downloadCanvas();
    });

    // Save button (if logged in)
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            saveDesign();
        });
    }

    // Update controls when object is selected
    canvas.on('selection:created', updateControls);
    canvas.on('selection:updated', updateControls);
}

function addText(text = 'نص جديد') {
    const textObject = new fabric.IText(text, {
        left: canvas.width / 2,
        top: canvas.height / 2,
        fontFamily: 'Cairo',
        fontSize: 40,
        fill: document.getElementById('text-color').value,
        originX: 'center',
        originY: 'center'
    });

    canvas.add(textObject);
    canvas.setActiveObject(textObject);
    canvas.renderAll();
}

function updateControls(e) {
    const activeObject = e.selected[0];
    if (activeObject && activeObject.type === 'i-text') {
        document.getElementById('text-color').value = activeObject.fill;
        document.getElementById('font-family').value = activeObject.fontFamily;
        document.getElementById('font-size').value = activeObject.fontSize;
        document.getElementById('font-size-value').textContent = activeObject.fontSize;
    }
}

function downloadCanvas() {
    // Increment download count
    if (TEMPLATE_DATA.id) {
        fetch(SITE_URL + '/api/increment-download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ template_id: TEMPLATE_DATA.id })
        });
    }

    // Download
    const dataURL = canvas.toDataURL({
        format: 'png',
        quality: 1,
        multiplier: 2
    });

    const link = document.createElement('a');
    link.download = 'greeting-card.png';
    link.href = dataURL;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function saveDesign() {
    if (!IS_LOGGED_IN) {
        alert('يجب تسجيل الدخول لحفظ التصميم');
        return;
    }

    const canvasData = JSON.stringify(canvas.toJSON());
    const previewImage = canvas.toDataURL({ format: 'png', quality: 0.5 });

    fetch(SITE_URL + '/api/save-design.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            template_id: TEMPLATE_DATA.id,
            canvas_data: canvasData,
            preview_image: previewImage
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم حفظ التصميم بنجاح!');
            } else {
                alert('حدث خطأ أثناء الحفظ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الحفظ');
        });
}

function hideLoadingSpinner() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}
