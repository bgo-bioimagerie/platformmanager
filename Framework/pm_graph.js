/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function PMPoint(x, y) {
    this.x = x;
    this.y = y;
}

function PMSize(width, height) {
    this.width = width;
    this.height = height;
}

function PMText(txt, font, fontsize, color) {
    this.txt = txt;
    this.font = font;
    this.fontsize = fontsize;
    this.color = color;
}

function PMNodeStyle(fillcolor, borderwidth, bordercolor, rounded) {
    this.fillcolor = fillcolor;
    this.borderwidth = borderwidth;
    this.bordercolor = bordercolor;
    this.rounded = rounded;
}

function PMArcStyle(width, color, rounded) {
    this.width = width;
    this.color = color;
    this.rounded = rounded;
}

function PMNode(id, shape, center, size, style, txt, hoverstyle) {
    this.id = id;         // unique ID
    this.shape = shape;   // shape name
    this.center = center; // PMPoint
    this.size = size;     // PMSize
    this.style = style; // PMNodeStyle
    this.txt = txt;     // PMText
    this.hoverstyle = hoverstyle; // PMNodeStyle
}

function PMArc(id, node_start_id, node_start_side, node_end_id, node_end_side, style) {
    this.id = id;
    this.node_start_id = node_start_id;
    this.node_start_side = node_start_side;
    this.node_end_id = node_end_id;
    this.node_end_side = node_end_side;
    this.style = style;
}

function PMGraph(canvas) {
    this.canvas = canvas;
    this.nodes = [];
    this.nodesMap = new Map();
    this.arcs = [];
    this.index = 0;

    this.addNode = function (id, shape, center, size, style, txt, hoverstyle) {
        this.nodes.push(new PMNode(id, shape, center, size, style, txt, hoverstyle));
        this.nodesMap.set(id, this.index);
        this.index++;
    };

    this.addArc = function (id, node_start_id, node_start_side, node_end_id, node_end_side, style) {
        this.arcs.push(new PMArc(id, node_start_id, node_start_side, node_end_id, node_end_side, style));
    };

    this.renderNode = function (node) {
        if (node.shape === "rectangle") {
            this.canvas.drawRect({
                layer: true,
                fillStyle: node.style.fillcolor,
                strokeStyle: node.style.bordercolor,
                strokeWidth: node.style.borderwidth,
                x: node.center.x,
                y: node.center.y,
                fromCenter: true,
                width: node.size.width,
                height: node.size.height,
                cornerRadius: node.style.rounded,
                click: function (layer) {
                    //$(node.id + "button").get(0).click();
                    $("#button_"+node.id).trigger('click');
                },
                mouseover: function (layer) {
                    $(this).animateLayer(layer, {
                        fillStyle: node.hoverstyle.fillcolor,
                        strokeStyle: node.hoverstyle.bordercolor,
                        strokeWidth: node.hoverstyle.borderwidth,
                        cornerRadius: node.hoverstyle.rounded
                    }, 100);
                },
                mouseout: function (layer) {
                    $(this).animateLayer(layer, {
                        fillStyle: node.style.fillcolor,
                        strokeStyle: node.style.bordercolor,
                        strokeWidth: node.style.borderwidth,
                        cornerRadius: node.style.rounded
                    }, 100);
                }
            });
        }
        this.canvas.drawText({
            layer: true,
            text: node.txt.txt,
            fontFamily: node.txt.font,
            fontSize: 18,
            x: node.center.x, y: node.center.y,
            fillStyle: node.txt.color,
            strokeStyle: node.txt.color,
            strokeWidth: 0
        });
    };

    this.renderArc = function (arc) {


        // node start position
        let startNodeCenter = this.nodes[this.nodesMap.get(arc.node_start_id)].center;
        let x1, y1;
        if (arc.node_start_side === "center") {
            x1 = startNodeCenter.x;
            y1 = startNodeCenter.y;
        }
        if (arc.node_start_side === "left") {
            x1 = startNodeCenter.x - this.nodes[this.nodesMap.get(arc.node_start_id)].size.height;
            y1 = startNodeCenter.y;
        }
        if (arc.node_start_side === "top") {
            x1 = startNodeCenter.x;
            y1 = startNodeCenter.y - this.nodes[this.nodesMap.get(arc.node_start_id)].size.width / 4;
        }
        if (arc.node_start_side === "right") {
            x1 = startNodeCenter.x + this.nodes[this.nodesMap.get(arc.node_start_id)].size.height;
            y1 = startNodeCenter.y;
        }
        if (arc.node_start_side === "bottom") {
            x1 = startNodeCenter.x;
            y1 = startNodeCenter.y + this.nodes[this.nodesMap.get(arc.node_start_id)].size.width / 4;
        }

        // node end position
        let endNodeCenter = this.nodes[this.nodesMap.get(arc.node_end_id)].center;
        let x2,y2;
        if (arc.node_end_side === "center") {
            x2 = endNodeCenter.x;
            y2 = endNodeCenter.y;
        }
        if (arc.node_end_side === "left") {
            x2 = endNodeCenter.x - this.nodes[this.nodesMap.get(arc.node_end_id)].size.height;
            y2 = endNodeCenter.y;
        }
        if (arc.node_end_side === "top") {
            x2 = endNodeCenter.x;
            y2 = endNodeCenter.y - this.nodes[this.nodesMap.get(arc.node_end_id)].size.width / 4;
        }
        if (arc.node_end_side === "right") {
            x2 = endNodeCenter.x + this.nodes[this.nodesMap.get(arc.node_end_id)].size.height;
            y2 = endNodeCenter.y;
        }
        if (arc.node_end_side === "bottom") {
            x2 = endNodeCenter.x;
            y2 = endNodeCenter.y + this.nodes[this.nodesMap.get(arc.node_end_id)].size.width / 4;
        }
        this.canvas.drawLine({
            layer: true,
            strokeStyle: arc.style.color,
            strokeWidth: arc.style.width,
            rounded: arc.style.rounded,
            closed: false,
            x1: x1, y1: y1,
            x2: x2, y2: y2
        });
    };

    this.render = function () {

        for (let i = 0; i < this.arcs.length; i++) {
            this.renderArc(this.arcs[i]);
        }
        for (let i = 0; i < this.nodes.length; i++) {
            this.renderNode(this.nodes[i]);
        }

    };
}
