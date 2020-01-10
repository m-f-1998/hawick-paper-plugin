/* Articles Upload ------------------------------- */
/* Author: Matthew Frankland --------------------- */

var articles;
while(articles == null) {
  articles = prompt( "How Many Articles Will Be Created?", "" );
}

/**
 *
 * [updateInput Store Form Input In Local Field]
 *
 * @param  {[Form Object]} obj [Input Field]
 *
 * @param  {[String]} val [User Input]
 *
 */
function updateInput( obj, val ){
    obj.defaultValue = val;
}

/**
 *
 * [createArticleInputs Create A Table For Each Article That Is To Be Uploaded]
 *
 * @param  {[Integer]} n [Number Of Articles]
 *
 */
function createArticleInputs( n ) {
  var i;
  for ( i = 0; i < n; ++i ) {
    var html =
      `<table class="hp-table">
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Article Title</th>
            <input type="hidden" value=` + n + ` name="article-index">
            <td class="hp-row-title"><input style="width:100%;height: 50px;" onchange="updateInput(this, this.value)" name="article-title-` + i + `"/></td>
          </tr>
          <tr valign="top"></tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Article Body</th>
            <td class="hp-row-body"><textarea class="hp-text-area" id="hp-row-body-` + i + `" name="article-body-` + i + `"></textarea></td>
          </tr>
          <tr valign="top"></tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Required Membership</th>
            <td class="hp-row-membership">
              Subscriber <input type="checkbox" onchange="updateInput(this, this.value)" name="article-membership-subscriber-` + i + `">  Subscriber (1 Year) <input type="checkbox" onchange="updateInput(this, this.value)" name="article-membership-subscriber-year-` + i + `"></td>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Date and Time For Publishing</th>
            <td class="hp-row-time">
              <input type="date" class="todays-date" placeholder="yyyy-mm-dd" max="9999-12-31" onchange="updateInput(this, this.value)" name="article-date-` + i + `"/>
              <input type="time" value="01:19" placeholder="hh:mm" max="23:59" onchange="updateInput(this, this.value)" name="article-time-` + i + `"/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Picture</th>
            <td class="hp-row-file">
              <input type="hidden" id="article-image-` + i + `" name="article-image-` + i + `">
              <button type="button" id="article-image-button-` + i + `">Select Image</button>
              <p id="image-name-` + i + `"></p>
            </td>
          </tr>
          <tr valign="top"></tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Category Title</th>
            <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-title-` + i + `"/></td>
          </tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Written By</th>
            <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-written-by-` + i + `"/></td>
          </tr>
          <tr valign="top">
            <th scope="row" class="hp-upload-title">Snippet</th>
            <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-snippet-` + i + `"/></td>
          </tr>`;
    var queryDict = {};
    location.search.substr(1).split("&").forEach(function(item) {queryDict[item.split("=")[0]] = item.split("=")[1]});
    if ( queryDict[ 'page' ] == 'sports-articles' ) {
      html +=
      `<tr valign="top">
        <th scope="row" class="hp-upload-title">Sport Category (e.g Football round-up, Rugby etc)</th>
        <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-sport-category-` + i + `"/></td>
      </tr>
       <tr valign="top">
         <th scope="row" class="hp-upload-title">Home Team</th>
         <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-home-team-` + i + `"/></td>
       </tr>
       <tr valign="top">
         <th scope="row" class="hp-upload-title">Home Score</th>
         <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-home-score-` + i + `"/></td>
       </tr>
       <tr valign="top">
         <th scope="row" class="hp-upload-title">Away Team</th>
         <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-away-team-` + i + `"/></td>
       </tr>
       <tr valign="top">
         <th scope="row" class="hp-upload-title">Away Score</th>
         <td class="hp-row-title"><input style="width:100%;height: 30px;" onchange="updateInput(this, this.value)" name="category-away-score-` + i + `"/></td>
       </tr>`;
   }
   document.getElementById( "hp-news-form" ).innerHTML += (html + '</table>');
  }
  document.getElementById( "hp-news-form" ).innerHTML += '<input type="submit" value="Submit" name="submit">';
}

/**
 *
 * [open_media_window Select Featured Image Of Post]
 *
 * @return {[Boolean]} [Prevent Default Behaviour]
 *
 */
function open_media_window( event ) {
  if ( this.window === undefined ) {
    this.window = wp.media( {
      title: 'Add Media',
      library: { type: 'image' },
      multiple: false,
      button: { text: 'Insert' }
    } );

    var self = this;
    this.window.on( 'select', function() {
      var first = self.window.state().get( 'selection' ).first().toJSON();
      if ( first.type == "image" ) {
        event.data.obj.defaultValue = first.id;
        event.data.title.innerHTML = "Image Selected - " + first.filename;
      } else {
        alert( "Featured Image Must Be An IMAGE!! 🙃" );
      }
    } );
  }

  this.window.open();
  return false;
}

/**
 *
 * [On Ready Create Article Input Fields And Add WP Editor]
 *
 */
jQuery( document ).ready( function($) {
  var x = parseInt( articles ); // Number Of Articles
  var i;
  if ( !isNaN( x ) ) {
    createArticleInputs( x );
    for ( i = 0; i < x; ++i ) {
      $('#article-image-button-' + i).click( { obj: $( '#article-image-' + i)[0], title: $( '#image-name-' + i)[0] }, open_media_window);
      wp.editor.initialize( "hp-row-body-" + i, {
        tinymce: {
          wpautop: true,
          plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
          toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr wp_more | alignleft aligncenter alignright | link unlink | fullscreen | wp_adv',
          toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help'
        },
        quicktags: true,
        mediaButtons: true,
      } );
    }
  }
});
