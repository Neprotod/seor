<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:param name="dir" select="filemanager/@path_utf" />
    <xsl:template match="/">
        <div id="filesystem_full" root="{filemanager/@parent_root}" path="{filemanager/@dir_path}" dir="{$dir}">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="">В начало</a>
                    </li>
                    <xsl:apply-templates select="filemanager/breadcrumb" />
                </ol>
            </nav>
            <xsl:call-template name="menu" />
            <table class="table table-hover">
                <xsl:apply-templates select="filemanager/dir" />
            </table>
            <hr />
            <table class="table table-hover">
                <xsl:apply-templates select="filemanager/file" />
            </table>
        </div>
        <script src="{filemanager/@mod_path}js/filesystem.js"></script>
    </xsl:template>

    <xsl:template match="breadcrumb">
        <xsl:choose>
            <xsl:when test="@url">
                <li class="breadcrumb-item">
                    <a href="{@url}"><xsl:value-of select="@name" /></a>
                </li>
            </xsl:when>
            <xsl:otherwise>
                <li class="breadcrumb-item active">
                    <xsl:value-of select="@name" />
                </li>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="dir">
        <tr class="dir" url="{@dir}">
            <td class="align-middle">
                <div class="icon">
                    <xsl:value-of select="//technical/svg/category" disable-output-escaping="yes" />
                </div>
            </td>
            <td class="card-header">
                <div class="text">
                    <span url="{@dir}" class="string"><xsl:value-of select="@name" disable-output-escaping="yes" /></span>
                </div>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="file">
        <tr class="file" url="{@file}">
            <xsl:choose>
                <xsl:when test="(@exp = 'jpg') or (@exp = 'png')">
                    <td class="align-middle">
                        <div class="icon_image">
                            <img src="/{$dir}{@file}" />
                        </div>
                    </td>
                </xsl:when>
                <xsl:otherwise>
                    <td class="align-middle">
                        <div class="icon">
                            <xsl:value-of select="//technical/svg/page" disable-output-escaping="yes" />
                        </div>
                    </td>
                </xsl:otherwise>
            </xsl:choose>
            <td class="align-middle">
                <div class="exp">
                    <xsl:value-of select="@exp" disable-output-escaping="yes" />
                </div>
            </td>
            <td class="card-header align-middle">
                <div class="text">
                    <span url="{@file}" class="string"><xsl:value-of select="@name" disable-output-escaping="yes" /></span>
                </div>
            </td>
            <td class="align-middle">
                <div class="exp">
                    <xsl:value-of select="@size" disable-output-escaping="yes" />
                </div>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template name="menu">
        <div id="filesystem_menu" class="d-flex">
            <div class="menu_item">
                <button id="file_button" type="file" class="btn btn-outline-secondary">Загрузить файл</button>
                <input for="file_button" type="file" name="test" style="display:none;" />
            </div>
            <div class="menu_item">
                <button id="drop_file_button" type="file" class="btn btn-outline-secondary">Удалить</button>
            </div>
            <div class="menu_item">
                <button id="rename_file_button" type="file" class="btn btn-outline-secondary">Переименовать</button>
            </div>
            <div class="menu_item">
                <button id="dir_file_button" type="file" class="btn btn-outline-secondary">Создать папку</button>
            </div>
        </div>
        
    </xsl:template>
    
    <xsl:template match="*"></xsl:template>
    
</xsl:stylesheet>