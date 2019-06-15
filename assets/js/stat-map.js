import Datamap from 'datamaps';

var arcs = new Datamap({
    element: document.getElementById("europe-flights-map"),
    scope: 'world',
    fills: {
        defaultFill: "rgba(70,133,255,0.48)",
    },
    setProjection: function(element) {
        var projection = d3.geo.equirectangular()
            .center([10, 5])
            .rotate([-10, -45])
            .scale(1000)
            .translate([element.offsetWidth / 2, element.offsetHeight / 2]);
        var path = d3.geo.path()
            .projection(projection);

        return {path: path, projection: projection};
    },
});

// Arcs coordinates can be specified explicitly with latitude/longtitude,
// or just the geographic center of the state/country.
arcs.arc(routes,  {arcSharpness: 0.5});
