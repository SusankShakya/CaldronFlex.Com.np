/**
 * Area Calculator - Visual area input tool
 * Allows users to draw shapes and calculate areas visually
 */

class AreaCalculator {
    constructor(options) {
        this.options = {
            container: '#area-calculator-container',
            unit: 'sqft',
            gridSize: 20,
            scale: 10, // 1 grid unit = 10 sqft
            ...options
        };
        
        this.shapes = [];
        this.currentShape = null;
        this.isDrawing = false;
        this.selectedTool = 'rectangle';
        this.canvas = null;
        this.ctx = null;
        
        this.init();
    }
    
    init() {
        this.createUI();
        this.setupCanvas();
        this.bindEvents();
        this.drawGrid();
    }
    
    createUI() {
        const container = $(this.options.container);
        container.html(`
            <div class="area-calculator-wrapper">
                <div class="area-calculator-toolbar">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary tool-btn active" data-tool="rectangle">
                            <i data-feather="square" class="icon-16"></i> Rectangle
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary tool-btn" data-tool="polygon">
                            <i data-feather="hexagon" class="icon-16"></i> Polygon
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary tool-btn" data-tool="circle">
                            <i data-feather="circle" class="icon-16"></i> Circle
                        </button>
                    </div>
                    
                    <div class="btn-group ms-3" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-shapes">
                            <i data-feather="trash-2" class="icon-16"></i> Clear
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="undo-shape">
                            <i data-feather="rotate-ccw" class="icon-16"></i> Undo
                        </button>
                    </div>
                    
                    <div class="scale-control ms-3">
                        <label class="form-label mb-0 me-2">Scale: 1 grid = </label>
                        <input type="number" class="form-control form-control-sm d-inline-block" 
                               id="grid-scale" value="${this.options.scale}" style="width: 60px;">
                        <span class="ms-1">${this.options.unit}</span>
                    </div>
                </div>
                
                <div class="area-calculator-canvas-wrapper">
                    <canvas id="area-calculator-canvas" width="600" height="400"></canvas>
                </div>
                
                <div class="area-calculator-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Shapes:</h6>
                            <div id="shapes-list" class="shapes-list"></div>
                        </div>
                        <div class="col-md-6">
                            <h6>Total Area:</h6>
                            <div class="total-area-display">
                                <h3 id="total-area">0</h3>
                                <span>${this.options.unit}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    setupCanvas() {
        this.canvas = document.getElementById('area-calculator-canvas');
        this.ctx = this.canvas.getContext('2d');
        
        // Set canvas size
        const wrapper = this.canvas.parentElement;
        this.canvas.width = wrapper.offsetWidth;
        this.canvas.height = 400;
    }
    
    bindEvents() {
        // Tool selection
        $('.tool-btn').on('click', (e) => {
            $('.tool-btn').removeClass('active');
            $(e.currentTarget).addClass('active');
            this.selectedTool = $(e.currentTarget).data('tool');
            this.cancelCurrentShape();
        });
        
        // Canvas events
        $(this.canvas).on('mousedown', this.onMouseDown.bind(this));
        $(this.canvas).on('mousemove', this.onMouseMove.bind(this));
        $(this.canvas).on('mouseup', this.onMouseUp.bind(this));
        $(this.canvas).on('click', this.onClick.bind(this));
        
        // Control buttons
        $('#clear-shapes').on('click', this.clearAll.bind(this));
        $('#undo-shape').on('click', this.undo.bind(this));
        $('#grid-scale').on('change', (e) => {
            this.options.scale = parseFloat(e.target.value) || 10;
            this.updateDisplay();
        });
    }
    
    drawGrid() {
        const gridSize = this.options.gridSize;
        const width = this.canvas.width;
        const height = this.canvas.height;
        
        this.ctx.strokeStyle = '#e0e0e0';
        this.ctx.lineWidth = 1;
        
        // Draw vertical lines
        for (let x = 0; x <= width; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, height);
            this.ctx.stroke();
        }
        
        // Draw horizontal lines
        for (let y = 0; y <= height; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(width, y);
            this.ctx.stroke();
        }
    }
    
    redraw() {
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Redraw grid
        this.drawGrid();
        
        // Redraw all shapes
        this.shapes.forEach(shape => this.drawShape(shape));
        
        // Draw current shape if drawing
        if (this.currentShape) {
            this.drawShape(this.currentShape, true);
        }
    }
    
    drawShape(shape, isTemp = false) {
        this.ctx.save();
        
        if (isTemp) {
            this.ctx.strokeStyle = '#007bff';
            this.ctx.setLineDash([5, 5]);
        } else {
            this.ctx.strokeStyle = '#28a745';
            this.ctx.fillStyle = 'rgba(40, 167, 69, 0.2)';
        }
        
        this.ctx.lineWidth = 2;
        
        switch (shape.type) {
            case 'rectangle':
                this.drawRectangle(shape, !isTemp);
                break;
            case 'polygon':
                this.drawPolygon(shape, !isTemp);
                break;
            case 'circle':
                this.drawCircle(shape, !isTemp);
                break;
        }
        
        this.ctx.restore();
    }
    
    drawRectangle(shape, fill = true) {
        const width = shape.endX - shape.startX;
        const height = shape.endY - shape.startY;
        
        this.ctx.beginPath();
        this.ctx.rect(shape.startX, shape.startY, width, height);
        if (fill) this.ctx.fill();
        this.ctx.stroke();
    }
    
    drawPolygon(shape, fill = true) {
        if (shape.points.length < 2) return;
        
        this.ctx.beginPath();
        this.ctx.moveTo(shape.points[0].x, shape.points[0].y);
        
        for (let i = 1; i < shape.points.length; i++) {
            this.ctx.lineTo(shape.points[i].x, shape.points[i].y);
        }
        
        if (shape.closed) {
            this.ctx.closePath();
            if (fill) this.ctx.fill();
        }
        
        this.ctx.stroke();
        
        // Draw points
        shape.points.forEach(point => {
            this.ctx.beginPath();
            this.ctx.arc(point.x, point.y, 3, 0, 2 * Math.PI);
            this.ctx.fill();
        });
    }
    
    drawCircle(shape, fill = true) {
        const radius = Math.sqrt(
            Math.pow(shape.endX - shape.centerX, 2) + 
            Math.pow(shape.endY - shape.centerY, 2)
        );
        
        this.ctx.beginPath();
        this.ctx.arc(shape.centerX, shape.centerY, radius, 0, 2 * Math.PI);
        if (fill) this.ctx.fill();
        this.ctx.stroke();
    }
    
    onMouseDown(e) {
        const pos = this.getMousePos(e);
        
        switch (this.selectedTool) {
            case 'rectangle':
                this.startRectangle(pos);
                break;
            case 'circle':
                this.startCircle(pos);
                break;
        }
    }
    
    onMouseMove(e) {
        if (!this.isDrawing || !this.currentShape) return;
        
        const pos = this.getMousePos(e);
        
        switch (this.selectedTool) {
            case 'rectangle':
                this.updateRectangle(pos);
                break;
            case 'circle':
                this.updateCircle(pos);
                break;
        }
        
        this.redraw();
    }
    
    onMouseUp(e) {
        if (!this.isDrawing || !this.currentShape) return;
        
        this.isDrawing = false;
        
        // Add shape to list
        this.shapes.push(this.currentShape);
        this.currentShape = null;
        
        this.updateDisplay();
        this.redraw();
    }
    
    onClick(e) {
        if (this.selectedTool !== 'polygon') return;
        
        const pos = this.getMousePos(e);
        
        if (!this.currentShape) {
            // Start new polygon
            this.currentShape = {
                type: 'polygon',
                points: [pos],
                closed: false
            };
        } else {
            // Check if clicking near first point to close
            const firstPoint = this.currentShape.points[0];
            const distance = Math.sqrt(
                Math.pow(pos.x - firstPoint.x, 2) + 
                Math.pow(pos.y - firstPoint.y, 2)
            );
            
            if (distance < 10 && this.currentShape.points.length > 2) {
                // Close polygon
                this.currentShape.closed = true;
                this.shapes.push(this.currentShape);
                this.currentShape = null;
                this.updateDisplay();
            } else {
                // Add point
                this.currentShape.points.push(pos);
            }
        }
        
        this.redraw();
    }
    
    startRectangle(pos) {
        this.isDrawing = true;
        this.currentShape = {
            type: 'rectangle',
            startX: pos.x,
            startY: pos.y,
            endX: pos.x,
            endY: pos.y
        };
    }
    
    updateRectangle(pos) {
        this.currentShape.endX = pos.x;
        this.currentShape.endY = pos.y;
    }
    
    startCircle(pos) {
        this.isDrawing = true;
        this.currentShape = {
            type: 'circle',
            centerX: pos.x,
            centerY: pos.y,
            endX: pos.x,
            endY: pos.y
        };
    }
    
    updateCircle(pos) {
        this.currentShape.endX = pos.x;
        this.currentShape.endY = pos.y;
    }
    
    getMousePos(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }
    
    calculateShapeArea(shape) {
        const scale = this.options.scale;
        const gridSize = this.options.gridSize;
        let area = 0;
        
        switch (shape.type) {
            case 'rectangle':
                const width = Math.abs(shape.endX - shape.startX) / gridSize;
                const height = Math.abs(shape.endY - shape.startY) / gridSize;
                area = width * height * scale * scale;
                break;
                
            case 'polygon':
                if (shape.closed && shape.points.length > 2) {
                    // Shoelace formula
                    let sum = 0;
                    for (let i = 0; i < shape.points.length; i++) {
                        const j = (i + 1) % shape.points.length;
                        sum += shape.points[i].x * shape.points[j].y;
                        sum -= shape.points[j].x * shape.points[i].y;
                    }
                    area = Math.abs(sum / 2) / (gridSize * gridSize) * scale * scale;
                }
                break;
                
            case 'circle':
                const radius = Math.sqrt(
                    Math.pow(shape.endX - shape.centerX, 2) + 
                    Math.pow(shape.endY - shape.centerY, 2)
                ) / gridSize;
                area = Math.PI * radius * radius * scale * scale;
                break;
        }
        
        return area;
    }
    
    updateDisplay() {
        let totalArea = 0;
        const shapesList = $('#shapes-list');
        shapesList.empty();
        
        this.shapes.forEach((shape, index) => {
            const area = this.calculateShapeArea(shape);
            totalArea += area;
            
            const shapeItem = $(`
                <div class="shape-item d-flex justify-content-between align-items-center mb-2">
                    <span>${shape.type} ${index + 1}: ${area.toFixed(2)} ${this.options.unit}</span>
                    <button class="btn btn-sm btn-outline-danger remove-shape" data-index="${index}">
                        <i data-feather="x" class="icon-16"></i>
                    </button>
                </div>
            `);
            
            shapesList.append(shapeItem);
        });
        
        // Update total area
        $('#total-area').text(totalArea.toFixed(2));
        
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Bind remove buttons
        $('.remove-shape').on('click', (e) => {
            const index = $(e.currentTarget).data('index');
            this.removeShape(index);
        });
    }
    
    removeShape(index) {
        this.shapes.splice(index, 1);
        this.updateDisplay();
        this.redraw();
    }
    
    clearAll() {
        this.shapes = [];
this.isDrawing = false;
        this.updateDisplay();
        this.redraw();
    }
    
    undo() {
        if (this.shapes.length > 0) {
            this.shapes.pop();
            this.updateDisplay();
            this.redraw();
        }
    }
    
    cancelCurrentShape() {
        this.currentShape = null;
        this.isDrawing = false;
        this.redraw();
    }
    
    getTotalArea() {
        let totalArea = 0;
        this.shapes.forEach(shape => {
            totalArea += this.calculateShapeArea(shape);
        });
        return totalArea;
    }
}

// Add CSS styles
const areaCalculatorStyles = `
<style>
.area-calculator-wrapper {
    padding: 15px;
}

.area-calculator-toolbar {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.area-calculator-canvas-wrapper {
    border: 2px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
}

#area-calculator-canvas {
    display: block;
    cursor: crosshair;
    background-color: #fff;
}

.area-calculator-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.shapes-list {
    max-height: 200px;
    overflow-y: auto;
}

.shape-item {
    background-color: #fff;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.total-area-display {
    text-align: center;
    padding: 20px;
    background-color: #fff;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.total-area-display h3 {
    margin: 0;
    color: #28a745;
    font-size: 2rem;
}

.scale-control {
    display: flex;
    align-items: center;
}

.scale-control .form-label {
    white-space: nowrap;
}
</style>
`;

// Inject styles when the script loads
if (!document.getElementById('area-calculator-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'area-calculator-styles';
    styleElement.innerHTML = areaCalculatorStyles;
    document.head.appendChild(styleElement.firstChild);
}
        this.currentShape = null;