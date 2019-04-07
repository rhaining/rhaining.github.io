function populateResume(data) {
  var keys = ["name", "subheader", "location", "blurb"];
  for(var i=0; i < keys.length; i++) {
    var key = keys[i]
    document.getElementById(key).innerHTML = data[key];
  }

  var logosHTML = renderLogosFromDepartments(data["departments"]);
  document.getElementById("logo_cloud").innerHTML = logosHTML;

  var departmentsHTML = renderDepartments(data["departments"]);
  document.getElementById("sections_wrapper").innerHTML = departmentsHTML;
}

function renderLogosFromDepartments(departments) {
  var buffer = "";
  for(var i=0; i < departments.length; i++) {
    var department = departments[i];
    if(department["title"] == "work") {
      var sections = department["sections"]
      for(var j=0; j < sections.length; j++) {
        var subsections = sections[j]["subsections"];
        for(var k=0; k < subsections.length; k++) {
          buffer += renderLogosFromSubsection(subsections[k]);
        }
      }
    }
  }
  return buffer;
}

function removeExtension(name) {
  return name.split('.').slice(0, -1).join('.')
}
function renderLogoImgTagFromSubsection(subsection) {
  if("logo" in subsection) {
    var title = "title" in subsection ? subsection["title"] : "";
    var style = "logo_style" in subsection ? subsection["logo_style"] : "";
    var imgClass = removeExtension(subsection["logo"])
    return `<img src="logos/${subsection["logo"]}" class="logo logo-${imgClass}" alt="${title}" title="${title}" style="${style}"/>`;
  } else {
    return ""
  }
}

function renderLogosFromSubsection(subsection) {
  var buffer = "";
  if("logo" in subsection) {
    var imgTag = renderLogoImgTagFromSubsection(subsection);
    buffer += `<a href="#${subsection["logo"]}">${imgTag}</a>`;
  }
  if("subsections" in subsection) {
    for(var k=0; k < subsection["subsections"].length; k++) {
      buffer += renderLogosFromSubsection(subsection["subsections"][k]);
    }
  }
  return buffer;
}

function renderDepartments(departments) {
  var buffer = "";
  for(var i=0; i < departments.length; i++) {
    var department = departments[i];
    buffer += `<div class="department">`;
    buffer += renderElement("div", "section_header", department["title"])
    buffer += renderSections(department["sections"]);
    buffer += `
    </div>
    `;
  }
  return buffer;
}

function renderSections(sections) {
  var buffer = "";
  for(var i=0; i < sections.length; i++) {
    var section = sections[i];
    buffer += `<div class="section">`;
    buffer += renderElement("div", "dates", section["dates"])
    var subsections = section["subsections"];
    for(var j=0; j < subsections.length; j++) {
      buffer += renderSubsection(subsections[j], false)
    }
    buffer += `</div>`;
  }
  return buffer
}

function renderSubsection(subsection, inset) {
  var buffer = `<a name="${subsection["logo"]}"/><div class="subsection">`;
  var headerTag = (inset ? "h2" : "h1")
  if("logo" in subsection) {
    buffer += renderLogoImgTagFromSubsection(subsection);
  } else {
    buffer += renderElement(headerTag, "", subsection["title"])
  }
  buffer += renderElement("div", "section_subtitle", subsection["subtitle"])
  buffer += renderElement("div", "section_location", subsection["location"])
  buffer += renderElement("div", "dates", subsection["dates"])

  if("descriptions" in subsection) {
    for(var k=0; k < subsection["descriptions"].length; k++) {
      buffer += `
        <div class="description">
          ${subsection["descriptions"][k]}
        </div>
      `;
    }
  }

  buffer += `<div class="section_inset">`;

  if("subsections" in subsection) {
    for(var k=0; k < subsection["subsections"].length; k++) {
      buffer += renderSubsection(subsection["subsections"][k], true);
    }
  }
  buffer += "</div></div>";

  return buffer
}

function renderElement(tag, className, value) {
  if (typeof value !== 'undefined') {
    return `
    <${tag} class="${className}">
        ${value}
      </${tag}>
    `;
  } else {
    return ""
  }
}

function renderError(text) {
  document.getElementById("error").innerHTML = text;
}
